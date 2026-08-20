const reportEscape = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
}[character]));

const reportCurrency = (value) => new Intl.NumberFormat('en-LK', {
    style: 'currency', currency: 'LKR', minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const reportNumber = (value) => new Intl.NumberFormat('en-LK').format(Number(value ?? 0));

const reportDate = (value) => value ? new Date(`${value}T00:00:00`).toLocaleDateString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric',
}) : '—';

const reportSetText = (id, value) => {
    const element = document.getElementById(id);
    if (element) element.textContent = value;
};

const reportError = (message = '') => {
    const element = document.getElementById('reportsError');
    if (!element) return;
    element.textContent = message;
    element.classList.toggle('hidden', !message);
};

const reportTable = (columns, rows) => {
    if (!rows.length) return '<p class="py-5 text-sm text-slate-500">No data is available for the selected period.</p>';
    return `<table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500"><tr>${columns.map((column) => `<th class="px-4 py-3">${reportEscape(column.label)}</th>`).join('')}</tr></thead><tbody class="divide-y divide-slate-100">${rows.map((row) => `<tr class="text-slate-700">${columns.map((column) => `<td class="px-4 py-3 ${column.emphasis ? 'font-semibold text-slate-950' : ''}">${column.format ? column.format(row[column.key], row) : reportEscape(row[column.key] ?? '—')}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
};

const reportBars = (rows = [], formatter = reportNumber) => {
    if (!rows.length || !rows.some((row) => Number(row.total) > 0)) {
        return '<p class="py-5 text-sm text-slate-500">No activity was recorded for this period.</p>';
    }
    const maximum = Math.max(...rows.map((row) => Number(row.total)), 1);
    return rows.map((row) => `<div><div class="mb-1 flex items-center justify-between gap-4 text-sm"><span class="truncate font-medium text-slate-700">${reportEscape(row.label)}</span><span class="shrink-0 font-semibold text-slate-950">${formatter(row.total)}</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-cyan-500" style="width:${Math.max((Number(row.total) / maximum) * 100, Number(row.total) ? 2 : 0)}%"></div></div></div>`).join('');
};

const reportBreakdown = (rows = [], formatter = reportNumber) => {
    if (!rows.length) return '<p class="py-5 text-sm text-slate-500">No breakdown data is available.</p>';
    return rows.map((row) => `<div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3"><span class="text-sm font-medium text-slate-700">${reportEscape(String(row.label).replaceAll('_', ' '))}</span><span class="text-sm font-bold text-slate-950">${(row.formatter ?? formatter)(row.total)}</span></div>`).join('');
};

const reportMeta = {
    patients: {title: 'Patient report', description: 'Patient registrations, gender distribution, and recent registrations.'},
    appointments: {title: 'Appointment report', description: 'Appointment volumes, statuses, and doctor workload.'},
    revenue: {title: 'Revenue report', description: 'Invoicing, received payments, outstanding balances, and payment methods.'},
    pharmacy: {title: 'Pharmacy report', description: 'Medicine inventory, expiry indicators, dispensations, and stock volumes.'},
    laboratory: {title: 'Laboratory report', description: 'Laboratory request volumes, workflow statuses, and most-requested tests.'},
    staff: {title: 'Staff report', description: 'Active employees, daily attendance, and leave-request activity.'},
};

const reportPresentation = (type, data) => {
    switch (type) {
        case 'patients':
            return {
                chart: data.registrations_by_date,
                breakdown: data.by_gender,
                rows: data.recent_patients,
                columns: [
                    {key: 'patient_code', label: 'Patient code', emphasis: true},
                    {key: 'full_name', label: 'Patient'},
                    {key: 'gender', label: 'Gender'},
                    {key: 'phone', label: 'Phone'},
                    {key: 'registered_at', label: 'Registered', format: (value) => reportEscape(reportDate(String(value ?? '').slice(0, 10)))},
                ],
                exportRows: data.recent_patients.map((patient) => ({'Patient code': patient.patient_code, Patient: patient.full_name, Gender: patient.gender, Phone: patient.phone, Registered: patient.registered_at})),
            };
        case 'appointments':
            return {
                chart: data.by_date,
                breakdown: data.by_status,
                rows: data.by_doctor,
                columns: [{key: 'label', label: 'Doctor', emphasis: true}, {key: 'total', label: 'Appointments', format: (value) => reportNumber(value)}],
                exportRows: data.by_doctor.map((row) => ({Doctor: row.label, Appointments: row.total})),
            };
        case 'revenue':
            return {
                chart: data.payments_by_date,
                chartFormat: reportCurrency,
                breakdown: data.by_payment_method,
                breakdownFormat: reportCurrency,
                rows: data.invoice_statuses,
                columns: [{key: 'label', label: 'Invoice status', emphasis: true}, {key: 'total', label: 'Invoices', format: (value) => reportNumber(value)}],
                exportRows: data.payments_by_date.map((row) => ({Date: row.label, 'Payments received (LKR)': row.total})),
            };
        case 'pharmacy':
            return {
                chart: data.dispensations_by_date,
                breakdown: [{label: 'Active medicines', total: data.active_medicines}, {label: 'Inventory value', total: data.inventory_value, formatter: reportCurrency}, {label: 'Expired batches', total: data.expired_batches}, {label: 'Expiring in 30 days', total: data.expiring_batches}],
                rows: data.stock_by_medicine,
                columns: [{key: 'label', label: 'Medicine', emphasis: true}, {key: 'total', label: 'Available units', format: (value) => reportNumber(value)}],
                exportRows: data.stock_by_medicine.map((row) => ({Medicine: row.label, 'Available units': row.total})),
            };
        case 'laboratory':
            return {
                chart: data.requests_by_date,
                breakdown: data.by_status,
                rows: data.top_tests,
                columns: [{key: 'label', label: 'Laboratory test', emphasis: true}, {key: 'total', label: 'Requests', format: (value) => reportNumber(value)}],
                exportRows: data.top_tests.map((row) => ({'Laboratory test': row.label, Requests: row.total})),
            };
        case 'staff':
            return {
                chart: data.attendance_by_date,
                breakdown: data.attendance_by_status,
                rows: data.leave_by_status,
                columns: [{key: 'label', label: 'Leave status', emphasis: true}, {key: 'total', label: 'Requests', format: (value) => reportNumber(value)}],
                exportRows: data.attendance_by_date.map((row) => ({Date: row.label, 'Attendance records': row.total})),
            };
        default:
            return {chart: [], breakdown: [], rows: [], columns: [], exportRows: []};
    }
};

const reportOverview = (data) => {
    reportSetText('reportPatientTotal', reportNumber(data.patients.total));
    reportSetText('reportNewPatients', `${reportNumber(data.patients.registered_in_period)} new in selected period`);
    reportSetText('reportAppointments', reportNumber(data.appointments.total));
    reportSetText('reportCompletedAppointments', `${reportNumber(data.appointments.completed)} completed in selected period`);
    reportSetText('reportRevenue', reportCurrency(data.revenue.payments_received));
    reportSetText('reportOutstanding', `${reportCurrency(data.revenue.outstanding)} outstanding balance`);
    reportSetText('reportLabRequests', reportNumber(data.laboratory.requests));
    reportSetText('reportCompletedLabs', `${reportNumber(data.laboratory.completed)} completed in selected period`);
    reportSetText('reportExpiredBatches', reportNumber(data.pharmacy.expired_batches));
    reportSetText('reportDispensations', `${reportNumber(data.pharmacy.dispensations)} dispensations in selected period`);
    reportSetText('reportActiveEmployees', reportNumber(data.staff.active_employees));
    reportSetText('reportPendingLeaves', `${reportNumber(data.staff.pending_leave_requests)} pending leave requests`);
    reportSetText('reportPeriod', `${reportDate(data.period.from_date)} – ${reportDate(data.period.to_date)}`);
};

const reportRenderDetail = (type, data) => {
    const meta = reportMeta[type];
    const presentation = reportPresentation(type, data);
    reportSetText('reportDetailTitle', meta.title);
    reportSetText('reportDetailDescription', meta.description);
    document.getElementById('reportChart').innerHTML = reportBars(presentation.chart, presentation.chartFormat);
    document.getElementById('reportBreakdown').innerHTML = reportBreakdown(presentation.breakdown, presentation.breakdownFormat);
    document.getElementById('reportDetails').innerHTML = reportTable(presentation.columns, presentation.rows);
    return presentation.exportRows;
};

const reportCsv = (rows) => {
    if (!rows.length) return '';
    const headings = [...new Set(rows.flatMap((row) => Object.keys(row)))];
    const cell = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`;
    return [headings.map(cell).join(','), ...rows.map((row) => headings.map((heading) => cell(row[heading])).join(','))].join('\r\n');
};

export const initialiseReportsPages = async (apiRequest, user) => {
    const page = document.getElementById('reportsPage');
    if (!page) return;
    if (user?.role?.slug !== 'ADMIN') {
        reportError('Reports and analytics are available to administrators only.');
        return;
    }

    const form = document.getElementById('reportFilters');
    const typeSelect = document.getElementById('reportType');
    let exportRows = [];
    const load = async () => {
        reportError();
        const parameters = new URLSearchParams({from_date: document.getElementById('reportFromDate').value, to_date: document.getElementById('reportToDate').value});
        const type = typeSelect.value;
        try {
            const [overviewResponse, detailResponse] = await Promise.all([
                apiRequest(`/reports/overview?${parameters.toString()}`),
                apiRequest(`/reports/${type}?${parameters.toString()}`),
            ]);
            reportOverview(overviewResponse.data);
            exportRows = reportRenderDetail(type, detailResponse.data);
        } catch (exception) {
            reportError(exception.message ?? 'The report could not be loaded.');
        }
    };

    form.addEventListener('submit', (event) => { event.preventDefault(); load(); });
    typeSelect.addEventListener('change', load);
    document.getElementById('reportPrintButton').addEventListener('click', () => window.print());
    document.getElementById('reportExportButton').addEventListener('click', () => {
        if (!exportRows.length) {
            reportError('There is no detailed data to export for the selected report.');
            return;
        }
        const file = new Blob([reportCsv(exportRows)], {type: 'text/csv;charset=utf-8'});
        const download = document.createElement('a');
        download.href = URL.createObjectURL(file);
        download.download = `medora-${typeSelect.value}-report-${document.getElementById('reportFromDate').value}-to-${document.getElementById('reportToDate').value}.csv`;
        download.click();
        URL.revokeObjectURL(download.href);
    });
    await load();
};
