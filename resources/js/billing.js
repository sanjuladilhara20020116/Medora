const billingCurrency = (value) => `LKR ${Number(value ?? 0).toLocaleString('en-LK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
const billingEscape = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'}[character]));
const billingDate = (value) => value ? new Date(value).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : '—';
const billingDateTime = (value) => value ? new Date(value).toLocaleString('en-GB', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
const billingNumber = (value) => Number.parseFloat(value || 0) || 0;

const billingError = (element, message = '') => {
    if (!element) return;
    element.textContent = message;
    element.classList.toggle('hidden', !message);
};

const billingStatus = (status) => {
    const classes = {
        UNPAID: 'bg-amber-100 text-amber-800',
        PARTIALLY_PAID: 'bg-sky-100 text-sky-800',
        PAID: 'bg-emerald-100 text-emerald-800',
        CANCELLED: 'bg-slate-200 text-slate-700',
    };
    return `<span class="rounded-full px-2.5 py-1 text-xs font-semibold ${classes[status] ?? 'bg-slate-100 text-slate-700'}">${billingEscape(String(status ?? '').replaceAll('_', ' '))}</span>`;
};

const billingAllowed = (user) => ['ADMIN', 'ACCOUNTANT'].includes(user?.role?.slug);

const initialiseBillingIndex = async (apiRequest, user) => {
    const page = document.getElementById('billingIndexPage');
    if (!page || !billingAllowed(user)) return;

    const error = document.getElementById('billingIndexError');
    const table = document.getElementById('billingInvoicesTable');
    const pagination = document.getElementById('billingInvoicePagination');
    const previous = document.getElementById('billingInvoicePrev');
    const next = document.getElementById('billingInvoiceNext');
    const filters = document.getElementById('billingInvoiceFilters');
    let currentPage = 1;
    let meta = {};

    const setSummary = (summary) => {
        document.getElementById('billingInvoiceCount').textContent = summary.invoice_count ?? '0';
        document.getElementById('billingTotalInvoiced').textContent = billingCurrency(summary.total_invoiced);
        document.getElementById('billingTotalPaid').textContent = billingCurrency(summary.total_paid);
        document.getElementById('billingOutstanding').textContent = billingCurrency(summary.total_outstanding);
    };

    const load = async () => {
        billingError(error);
        const search = document.getElementById('billingInvoiceSearch').value.trim();
        const status = document.getElementById('billingInvoiceStatus').value;
        const query = new URLSearchParams({page: currentPage, per_page: 10});
        if (search) query.set('search', search);
        if (status) query.set('status', status);
        try {
            const [summaryResponse, invoicesResponse] = await Promise.all([
                apiRequest('/billing/summary'),
                apiRequest(`/billing/invoices?${query.toString()}`),
            ]);
            setSummary(summaryResponse.data);
            meta = invoicesResponse.meta ?? {};
            const invoices = invoicesResponse.data ?? [];
            table.innerHTML = invoices.length ? invoices.map((invoice) => `<tr class="text-sm text-slate-700"><td class="px-5 py-4 font-semibold text-slate-950">${billingEscape(invoice.invoice_code)}</td><td class="px-5 py-4"><p class="font-medium">${billingEscape(invoice.patient?.full_name)}</p><p class="text-xs text-slate-500">${billingEscape(invoice.patient?.patient_code)}</p></td><td class="px-5 py-4">${billingDate(invoice.issued_at)}</td><td class="px-5 py-4 font-medium">${billingCurrency(invoice.total_amount)}</td><td class="px-5 py-4">${billingCurrency(invoice.balance)}</td><td class="px-5 py-4">${billingStatus(invoice.status)}</td><td class="px-5 py-4"><a href="/billing/invoices/${invoice.id}" class="font-semibold text-cyan-700 hover:text-cyan-900">View</a></td></tr>`).join('') : '<tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No invoices found.</td></tr>';
            pagination.textContent = meta.total ? `Showing ${meta.from}–${meta.to} of ${meta.total} invoices` : 'No invoices found';
            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= (meta.last_page ?? 1);
        } catch (exception) {
            billingError(error, exception.message);
        }
    };

    filters.addEventListener('submit', (event) => { event.preventDefault(); currentPage = 1; load(); });
    previous.addEventListener('click', () => { if (currentPage > 1) { currentPage -= 1; load(); } });
    next.addEventListener('click', () => { if (currentPage < (meta.last_page ?? 1)) { currentPage += 1; load(); } });
    await load();
};

const initialiseBillingForm = async (apiRequest, user) => {
    const page = document.getElementById('billingInvoiceFormPage');
    if (!page || !billingAllowed(user)) return;

    const error = document.getElementById('billingInvoiceFormError');
    const form = document.getElementById('billingInvoiceForm');
    const patientSelect = document.getElementById('billingPatient');
    const chargeList = document.getElementById('billingChargesList');
    const chargeEmpty = document.getElementById('billingChargesEmpty');
    const manualItems = document.getElementById('billingManualItems');
    const total = document.getElementById('billingInvoiceTotal');
    let charges = [];

    const updateTotal = () => {
        const selectedTotal = [...chargeList.querySelectorAll('input[type="checkbox"]:checked')]
            .reduce((sum, checkbox) => sum + billingNumber(checkbox.dataset.lineTotal), 0);
        const manualTotal = [...manualItems.querySelectorAll('[data-manual-item]')].reduce((sum, row) => {
            return sum + billingNumber(row.querySelector('[name="quantity"]').value) * billingNumber(row.querySelector('[name="unit_price"]').value);
        }, 0);
        const discount = billingNumber(form.elements.discount_amount.value);
        const tax = billingNumber(form.elements.tax_amount.value);
        total.textContent = billingCurrency(Math.max(0, selectedTotal + manualTotal - discount + tax));
    };

    const addManualItem = () => {
        const row = document.createElement('div');
        row.dataset.manualItem = 'true';
        row.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[160px_1fr_120px_150px_auto]';
        row.innerHTML = '<select name="item_type" class="rounded-xl border border-slate-300 px-3 py-2"><option value="ADMISSION">Admission</option><option value="OTHER">Other</option></select><input name="description" required placeholder="Charge description" class="rounded-xl border border-slate-300 px-3 py-2"><input name="quantity" type="number" min="0.01" step="0.01" value="1" class="rounded-xl border border-slate-300 px-3 py-2"><input name="unit_price" type="number" min="0" step="0.01" value="0" class="rounded-xl border border-slate-300 px-3 py-2"><button type="button" class="rounded-xl border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Remove</button>';
        row.querySelector('button').addEventListener('click', () => { row.remove(); updateTotal(); });
        row.querySelectorAll('input, select').forEach((input) => input.addEventListener('input', updateTotal));
        manualItems.appendChild(row);
        updateTotal();
    };

    const renderCharges = () => {
        chargeList.innerHTML = charges.map((charge) => `<label class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-200 p-4 hover:border-cyan-300"><input type="checkbox" data-charge-type="${billingEscape(charge.type)}" data-charge-id="${charge.id}" data-line-total="${charge.line_total}" class="h-4 w-4 rounded border-slate-300 text-cyan-600"><span class="min-w-0 flex-1"><span class="block font-semibold text-slate-900">${billingEscape(charge.description)}</span><span class="mt-1 block text-sm text-slate-500">${billingEscape(charge.reference ?? charge.type.replaceAll('_', ' '))}</span></span><span class="font-bold text-slate-950">${billingCurrency(charge.line_total)}</span></label>`).join('');
        chargeList.querySelectorAll('input').forEach((input) => input.addEventListener('change', updateTotal));
        chargeEmpty.textContent = charges.length ? 'Select one or more charges to include in this invoice.' : 'No uninvoiced service charges are available for this patient. You can add a manual admission or other charge below.';
        chargeEmpty.classList.remove('hidden');
        updateTotal();
    };

    const loadCharges = async () => {
        charges = [];
        chargeList.innerHTML = '';
        if (!patientSelect.value) {
            chargeEmpty.textContent = 'Select a patient to load available charges.';
            chargeEmpty.classList.remove('hidden');
            updateTotal();
            return;
        }
        chargeEmpty.textContent = 'Loading available charges...';
        chargeEmpty.classList.remove('hidden');
        try {
            const response = await apiRequest(`/billing/available-charges?patient_id=${patientSelect.value}`);
            charges = response.data ?? [];
            renderCharges();
        } catch (exception) {
            billingError(error, exception.message);
            chargeEmpty.textContent = 'Available charges could not be loaded.';
        }
    };

    try {
        const response = await apiRequest('/billing/patients');
        (response.data ?? []).forEach((patient) => {
            const option = document.createElement('option');
            option.value = patient.id;
            option.textContent = `${patient.patient_code} · ${patient.full_name}`;
            patientSelect.appendChild(option);
        });
    } catch (exception) {
        billingError(error, exception.message);
    }

    patientSelect.addEventListener('change', loadCharges);
    document.getElementById('addBillingManualItem').addEventListener('click', addManualItem);
    form.elements.discount_amount.addEventListener('input', updateTotal);
    form.elements.tax_amount.addEventListener('input', updateTotal);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        billingError(error);
        const chargeSources = [...chargeList.querySelectorAll('input[type="checkbox"]:checked')].map((checkbox) => ({type: checkbox.dataset.chargeType, id: Number(checkbox.dataset.chargeId)}));
        const rows = [...manualItems.querySelectorAll('[data-manual-item]')];
        const manual = rows.map((row) => ({item_type: row.querySelector('[name="item_type"]').value, description: row.querySelector('[name="description"]').value.trim(), quantity: billingNumber(row.querySelector('[name="quantity"]').value), unit_price: billingNumber(row.querySelector('[name="unit_price"]').value)}));
        if (!chargeSources.length && !manual.length) {
            billingError(error, 'Select a service charge or add a manual admission/other charge.');
            return;
        }
        try {
            const response = await apiRequest('/billing/invoices', {method: 'POST', body: JSON.stringify({patient_id: Number(patientSelect.value), due_date: form.elements.due_date.value || null, discount_amount: billingNumber(form.elements.discount_amount.value), tax_amount: billingNumber(form.elements.tax_amount.value), notes: form.elements.notes.value.trim() || null, charge_sources: chargeSources, manual_items: manual})});
            window.location.assign(`/billing/invoices/${response.data.id}`);
        } catch (exception) {
            billingError(error, exception.message);
        }
    });
};

const initialiseBillingShow = async (apiRequest, user) => {
    const page = document.getElementById('billingInvoiceShowPage');
    if (!page || !billingAllowed(user)) return;

    const invoiceId = page.dataset.invoiceId;
    const error = document.getElementById('billingInvoiceShowError');
    const documentContainer = document.getElementById('billingInvoiceDocument');
    const paymentSection = document.getElementById('billingPaymentSection');
    const paymentForm = document.getElementById('billingPaymentForm');
    const cancelButton = document.getElementById('billingCancelInvoice');
    let invoice;

    const render = () => {
        document.title = `${invoice.invoice_code} | Medora HMS`;
        documentContainer.innerHTML = `<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">Medora Hospital Management</p><h2 class="mt-1 text-2xl font-bold text-slate-950">Invoice ${billingEscape(invoice.invoice_code)}</h2><p class="mt-1 text-sm text-slate-500">Issued ${billingDateTime(invoice.issued_at)}</p></div><div>${billingStatus(invoice.status)}</div></div><div class="grid gap-4 py-6 md:grid-cols-2"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Bill to</p><p class="mt-2 font-bold text-slate-950">${billingEscape(invoice.patient?.full_name)}</p><p class="text-sm text-slate-600">${billingEscape(invoice.patient?.patient_code)}${invoice.patient?.phone ? ` · ${billingEscape(invoice.patient.phone)}` : ''}</p>${invoice.patient?.address_line_1 ? `<p class="mt-1 text-sm text-slate-600">${billingEscape(invoice.patient.address_line_1)}${invoice.patient?.city ? `, ${billingEscape(invoice.patient.city)}` : ''}</p>` : ''}</div><div class="md:text-right"><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Due date</p><p class="mt-2 font-semibold text-slate-950">${billingDate(invoice.due_date)}</p><p class="mt-1 text-sm text-slate-600">Created by ${billingEscape(invoice.created_by || '—')}</p></div></div><div class="overflow-x-auto"><table class="min-w-full"><thead class="border-y border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Charge</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Unit price</th><th class="px-4 py-3 text-right">Amount</th></tr></thead><tbody class="divide-y divide-slate-100">${(invoice.items ?? []).map((item) => `<tr class="text-sm text-slate-700"><td class="px-4 py-4"><p class="font-medium text-slate-950">${billingEscape(item.description)}</p><p class="mt-1 text-xs text-slate-500">${billingEscape(item.item_type.replaceAll('_', ' '))}</p></td><td class="px-4 py-4 text-right">${billingEscape(item.quantity)}</td><td class="px-4 py-4 text-right">${billingCurrency(item.unit_price)}</td><td class="px-4 py-4 text-right font-semibold">${billingCurrency(item.line_total)}</td></tr>`).join('')}</tbody></table></div><div class="ml-auto mt-6 max-w-sm space-y-2 text-sm"><div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>${billingCurrency(invoice.subtotal)}</span></div><div class="flex justify-between"><span class="text-slate-500">Discount</span><span>− ${billingCurrency(invoice.discount_amount)}</span></div><div class="flex justify-between"><span class="text-slate-500">Tax</span><span>+ ${billingCurrency(invoice.tax_amount)}</span></div><div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-950"><span>Total</span><span>${billingCurrency(invoice.total_amount)}</span></div><div class="flex justify-between text-emerald-700"><span>Paid</span><span>${billingCurrency(invoice.paid_amount)}</span></div><div class="flex justify-between text-lg font-bold ${billingNumber(invoice.balance) ? 'text-amber-800' : 'text-emerald-700'}"><span>Balance</span><span>${billingCurrency(invoice.balance)}</span></div></div>${invoice.notes ? `<div class="mt-6 border-t border-slate-200 pt-5"><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Notes</p><p class="mt-2 whitespace-pre-wrap text-sm text-slate-600">${billingEscape(invoice.notes)}</p></div>` : ''}</section><section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-bold text-slate-950">Payment history</h3><div class="mt-4 space-y-3">${(invoice.payments ?? []).length ? invoice.payments.map((payment) => `<article class="flex flex-col gap-2 rounded-xl bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-950">${billingEscape(payment.payment_code)} · ${billingEscape(payment.payment_method.replaceAll('_', ' '))}</p><p class="mt-1 text-sm text-slate-500">${billingDateTime(payment.paid_at)}${payment.reference_number ? ` · ${billingEscape(payment.reference_number)}` : ''}${payment.received_by ? ` · received by ${billingEscape(payment.received_by)}` : ''}</p></div><p class="font-bold text-emerald-700">${billingCurrency(payment.amount)}</p></article>`).join('') : '<p class="text-sm text-slate-500">No payments recorded yet.</p>'}</div></section>`;
        const canPay = ['UNPAID', 'PARTIALLY_PAID'].includes(invoice.status) && billingNumber(invoice.balance) > 0;
        paymentSection.classList.toggle('hidden', !canPay);
        cancelButton.classList.toggle('hidden', invoice.status !== 'UNPAID');
        if (canPay) {
            paymentForm.elements.amount.max = invoice.balance;
            paymentForm.elements.amount.value = invoice.balance;
        }
    };

    const load = async () => {
        billingError(error);
        try {
            const response = await apiRequest(`/billing/invoices/${invoiceId}`);
            invoice = response.data;
            render();
        } catch (exception) {
            billingError(error, exception.message);
        }
    };

    paymentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        billingError(error);
        try {
            const response = await apiRequest(`/billing/invoices/${invoiceId}/payments`, {method: 'POST', body: JSON.stringify({amount: billingNumber(paymentForm.elements.amount.value), payment_method: paymentForm.elements.payment_method.value, reference_number: paymentForm.elements.reference_number.value.trim() || null, paid_at: paymentForm.elements.paid_at.value || null, notes: paymentForm.elements.notes.value.trim() || null})});
            invoice = response.data;
            paymentForm.reset();
            render();
        } catch (exception) {
            billingError(error, exception.message);
        }
    });
    cancelButton.addEventListener('click', async () => {
        if (!window.confirm(`Cancel invoice ${invoice.invoice_code}? This makes its source charges available for invoicing again.`)) return;
        try {
            const response = await apiRequest(`/billing/invoices/${invoiceId}/cancel`, {method: 'POST'});
            invoice = response.data;
            render();
        } catch (exception) {
            billingError(error, exception.message);
        }
    });
    document.getElementById('billingPrintInvoice').addEventListener('click', () => window.print());
    await load();
};

export const initialiseBillingPages = async (apiRequest, user) => {
    await initialiseBillingIndex(apiRequest, user);
    await initialiseBillingForm(apiRequest, user);
    await initialiseBillingShow(apiRequest, user);
};
