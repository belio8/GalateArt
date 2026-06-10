'use strict';

let comData = null;
let currentTier = null;
let charCount = 1;
let selectedOptions = {}; // { optionId: [itemId, itemId] }

document.addEventListener('DOMContentLoaded', () => {
    loadCommissionData();

    // Step 1: TOS agreement
    const agreeTos = document.getElementById('agreeTos');
    const btnNext = document.getElementById('btnNextStep1');
    if (agreeTos && btnNext) {
        agreeTos.addEventListener('change', (e) => {
            btnNext.disabled = !e.target.checked;
        });
        btnNext.addEventListener('click', () => {
            goToStep(2);
        });
    }

    // Step 2: Form validation & submit
    const understandCheck = document.getElementById('understandCheck');
    const btnSubmit = document.getElementById('btnSubmitOrder');
    if (understandCheck && btnSubmit) {
        understandCheck.addEventListener('change', checkFormValidity);
        btnSubmit.addEventListener('click', submitCommission);
    }

    const descInput = document.getElementById('descInput');
    if (descInput) {
        descInput.addEventListener('input', checkFormValidity);
    }
});

function goToStep(stepNum) {
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
    const targetStep = document.getElementById('step' + stepNum);
    if (targetStep) {
        targetStep.style.display = stepNum === 1 ? 'flex' : 'block';
        // Re-trigger the animation
        targetStep.style.animation = 'none';
        targetStep.offsetHeight; // force reflow
        targetStep.style.animation = '';
    }

    // Update step pills
    document.querySelectorAll('.com-step-pill').forEach(pill => {
        const pillStep = parseInt(pill.dataset.step);
        pill.classList.remove('active', 'done');
        if (pillStep === stepNum) {
            pill.classList.add('active');
        } else if (pillStep < stepNum) {
            pill.classList.add('done');
        }
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function loadCommissionData() {
    try {
        const res = await fetch(`api/commission-options.php?artist=${encodeURIComponent(ARTIST_USERNAME)}`);
        const data = await res.json();
        if (data.status === 'ok') {
            comData = data;
            renderTOS();
            renderForm();
        } else {
            alert(data.message || 'Gagal memuat data commission.');
        }
    } catch (e) {
        console.error('Failed to load commission data', e);
        alert('Terjadi kesalahan jaringan.');
    }
}

function renderTOS() {
    const tosEl = document.getElementById('tosContent');
    if (tosEl && comData.profile.tos) {
        tosEl.innerHTML = `<p>${escapeHtml(comData.profile.tos).replace(/\n/g, '<br>')}</p>`;
    } else if (tosEl) {
        tosEl.innerHTML = '<p>Tidak ada Terms of Service.</p>';
    }
}

function renderForm() {
    const container = document.getElementById('dynamicFormContainer');
    if (!container) return;

    let html = '';

    if (!comData.tiers || comData.tiers.length === 0) {
        container.innerHTML = '<div style="padding:30px; text-align:center; color:#ef4444; background:#2a2a35; border-radius:12px;">Artist ini belum mengatur Commission Tier. Anda belum bisa memesan.</div>';
        document.getElementById('btnSubmitOrder').style.display = 'none';
        return;
    }

    // 1. Tiers (Which one do you like?)
    html += `
    <div class="com-form-group">
        <h4 class="com-section-title">Which one do you like? <span class="required">*</span></h4>
        <div class="com-options-grid">
            ${comData.tiers.map((tier, index) => `
                <label class="com-option-card ${index === 0 ? 'selected' : ''}">
                    <input type="radio" name="tierSelection" value="${tier.id}" ${index === 0 ? 'checked' : ''} onchange="handleTierChange('${tier.id}')">
                    <div class="com-opt-content">
                        <div class="com-opt-radio"></div>
                        <div class="com-opt-text">
                            <strong>${escapeHtml(tier.name)}</strong>
                            <span class="com-opt-price">+Rp ${Number(tier.price).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </label>
            `).join('')}
        </div>
    </div>
    `;

    // 2. Character Count
    html += `
    <div class="com-form-group">
        <h4 class="com-section-title">How many characters do you want? <span class="required">*</span></h4>
        <p class="com-hint">Price will be multiplied from above pricing</p>
        <div class="com-counter">
            <button type="button" onclick="updateCharCount(-1)"><i class="fas fa-minus"></i></button>
            <input type="number" id="charCountInput" value="1" min="1" max="10" readonly>
            <button type="button" onclick="updateCharCount(1)"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    `;

    // 3. Dynamic Options
    comData.options.forEach(opt => {
        const isMultiple = opt.selection_type === 'multiple';
        const inputType = isMultiple ? 'checkbox' : 'radio';
        
        html += `
        <div class="com-form-group" data-opt-id="${opt.id}">
            <h4 class="com-section-title">${escapeHtml(opt.category)} ${parseInt(opt.is_required) ? '<span class="required">*</span>' : ''}</h4>
            ${opt.description ? `<p class="com-hint">${escapeHtml(opt.description)}</p>` : ''}
            <div class="com-options-grid">
                ${opt.items.map(item => {
                    const isChecked = parseInt(item.is_default) === 1 ? 'checked' : '';
                    const priceLabel = item.price_value > 0 
                        ? (item.price_type === 'fixed' ? `+Rp ${Number(item.price_value).toLocaleString('id-ID')}` : `+${item.price_value}%`) 
                        : (item.label.toLowerCase().includes('free') ? '' : 'Included');
                    
                    return `
                    <label class="com-option-card ${isChecked ? 'selected' : ''}">
                        <input type="${inputType}" name="opt_${opt.id}" value="${item.id}" ${isChecked} onchange="handleOptionChange(this, '${opt.id}', '${item.id}')">
                        <div class="com-opt-content">
                            <div class="com-opt-${inputType}"></div>
                            <div class="com-opt-text">
                                <strong>${escapeHtml(item.label)}</strong>
                                <span class="com-opt-price" style="${priceLabel ? '' : 'display:none;'}">${priceLabel}</span>
                            </div>
                        </div>
                    </label>
                    `;
                }).join('')}
            </div>
        </div>
        `;
    });

    container.innerHTML = html;

    // Initialize state
    if (comData.tiers.length > 0) {
        currentTier = comData.tiers[0];
    }
    
    // Initialize selected options based on defaults
    comData.options.forEach(opt => {
        selectedOptions[opt.id] = [];
        opt.items.forEach(item => {
            if (parseInt(item.is_default) === 1) {
                selectedOptions[opt.id].push(item.id);
            }
        });
    });

    updatePriceCalculation();
    checkFormValidity();
}

function handleTierChange(tierId) {
    currentTier = comData.tiers.find(t => t.id === tierId);
    
    // Update UI selected class
    document.querySelectorAll('input[name="tierSelection"]').forEach(el => {
        el.closest('.com-option-card').classList.toggle('selected', el.checked);
    });

    updatePriceCalculation();
}

function updateCharCount(delta) {
    const input = document.getElementById('charCountInput');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 10) val = 10;
    input.value = val;
    charCount = val;
    updatePriceCalculation();
}

function handleOptionChange(inputEl, optId, itemId) {
    const opt = comData.options.find(o => o.id === optId);
    const isMultiple = opt.selection_type === 'multiple';

    if (isMultiple) {
        if (inputEl.checked) {
            if (!selectedOptions[optId].includes(itemId)) {
                selectedOptions[optId].push(itemId);
            }
        } else {
            selectedOptions[optId] = selectedOptions[optId].filter(id => id !== itemId);
        }
    } else {
        selectedOptions[optId] = [itemId];
    }

    // Update UI selected class for this option group
    document.querySelectorAll(`input[name="opt_${optId}"]`).forEach(el => {
        el.closest('.com-option-card').classList.toggle('selected', el.checked);
    });

    updatePriceCalculation();
    checkFormValidity();
}

function updatePriceCalculation() {
    if (!currentTier) return;

    const baseTierPrice = parseFloat(currentTier.price);
    const charTierPrice = baseTierPrice * charCount;
    let addonTotal = 0;

    const summaryList = document.getElementById('summaryList');
    let summaryHtml = `
        <div class="com-summary-row">
            <span>Tier: ${escapeHtml(currentTier.name)} ${charCount > 1 ? `(x${charCount})` : ''}</span>
            <span>Rp ${charTierPrice.toLocaleString('id-ID')}</span>
        </div>
    `;

    comData.options.forEach(opt => {
        const selectedItemIds = selectedOptions[opt.id] || [];
        selectedItemIds.forEach(itemId => {
            const item = opt.items.find(i => i.id === itemId);
            if (item && parseFloat(item.price_value) > 0) {
                let itemCost = 0;
                let costLabel = '';
                if (item.price_type === 'fixed') {
                    itemCost = parseFloat(item.price_value);
                    costLabel = `Rp ${itemCost.toLocaleString('id-ID')}`;
                } else if (item.price_type === 'percent') {
                    itemCost = charTierPrice * (parseFloat(item.price_value) / 100);
                    costLabel = `Rp ${itemCost.toLocaleString('id-ID')} (+${item.price_value}%)`;
                }
                
                addonTotal += itemCost;
                summaryHtml += `
                    <div class="com-summary-row">
                        <span style="font-size:12px; color:var(--text-gray);">${escapeHtml(item.label)}</span>
                        <span style="font-size:12px; color:var(--text-gray);">${costLabel}</span>
                    </div>
                `;
            }
        });
    });

    const total = charTierPrice + addonTotal;
    
    summaryList.innerHTML = summaryHtml;
    document.getElementById('totalPriceDisplay').innerText = `Rp ${total.toLocaleString('id-ID')}`;
}

function checkFormValidity() {
    let isValid = true;

    // Check required options
    comData.options.forEach(opt => {
        if (parseInt(opt.is_required) === 1) {
            if (!selectedOptions[opt.id] || selectedOptions[opt.id].length === 0) {
                isValid = false;
            }
        }
    });

    // Check desc
    const desc = document.getElementById('descInput')?.value.trim();
    if (!desc) isValid = false;

    // Check understand
    const understand = document.getElementById('understandCheck')?.checked;
    if (!understand) isValid = false;

    document.getElementById('btnSubmitOrder').disabled = !isValid;
}

async function submitCommission() {
    const btn = document.getElementById('btnSubmitOrder');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    try {
        if (!currentTier) {
            throw new Error("Tier belum dipilih atau artist tidak memiliki tier aktif.");
        }

        // Format selected options for API
    const formattedOptions = [];
    comData.options.forEach(opt => {
        const selectedItemIds = selectedOptions[opt.id] || [];
        if (selectedItemIds.length > 0) {
            formattedOptions.push({
                category: opt.category,
                items: selectedItemIds.map(id => {
                    const item = opt.items.find(i => i.id === id);
                    return { id: item.id, label: item.label };
                })
            });
        }
    });

    // Determine NSFW (Find the opt with category NSFW)
    let isNsfw = 0;
    const nsfwOpt = comData.options.find(o => o.category.toUpperCase().includes('NSFW'));
    if (nsfwOpt && selectedOptions[nsfwOpt.id]) {
        const nsfwItem = nsfwOpt.items.find(i => i.id === selectedOptions[nsfwOpt.id][0]);
        if (nsfwItem && nsfwItem.label.toUpperCase() === 'YES') {
            isNsfw = 1;
        }
    }

    const formData = new FormData();
    formData.append('artist_username', ARTIST_USERNAME);
    formData.append('tier_id', currentTier.id);
    formData.append('character_count', charCount);
    formData.append('is_nsfw', isNsfw);
    formData.append('description', document.getElementById('descInput').value);
    formData.append('deadline', document.getElementById('deadlineInput').value);
    formData.append('selected_options', JSON.stringify(formattedOptions));

    // Append actual files
    const fileInput = document.getElementById('refFiles');
    if (fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('reference_files[]', fileInput.files[i]);
        }
    }

    const res = await fetch('api/submit-commission.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.status === 'ok') {
            document.getElementById('successOverlay').classList.add('show');
        } else {
            alert(data.message || 'Gagal mengirim commission.');
            btn.disabled = false;
            btn.innerHTML = 'Request Commission';
        }
    } catch (e) {
        console.error(e);
        alert(e.message || 'Terjadi kesalahan saat memproses order.');
        btn.disabled = false;
        btn.innerHTML = 'Request Commission';
    }
}
