const staffEscape = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'}[character]));
const staffDate = (value) => value ? new Date(`${value}`.length === 10 ? `${value}T00:00:00` : value).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : '—';
const staffDateTime = (value) => value ? new Date(value).toLocaleString('en-GB', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
const staffError = (element, message = '') => { if (element) { element.textContent = message; element.classList.toggle('hidden', !message); } };
const staffAllowed = (user) => user?.role?.slug === 'ADMIN';
const staffStatus = (status) => {
    const classes = {ACTIVE: 'bg-emerald-100 text-emerald-800', INACTIVE: 'bg-slate-200 text-slate-700', SUSPENDED: 'bg-red-100 text-red-800', PRESENT: 'bg-emerald-100 text-emerald-800', LATE: 'bg-amber-100 text-amber-800', ABSENT: 'bg-red-100 text-red-800', ON_LEAVE: 'bg-sky-100 text-sky-800', PENDING: 'bg-amber-100 text-amber-800', APPROVED: 'bg-emerald-100 text-emerald-800', REJECTED: 'bg-red-100 text-red-800'};
    return `<span class="rounded-full px-2.5 py-1 text-xs font-semibold ${classes[status] ?? 'bg-slate-100 text-slate-700'}">${staffEscape(String(status ?? '').replaceAll('_', ' '))}</span>`;
};
const staffOption = (select, value, label) => { const option = document.createElement('option'); option.value = value; option.textContent = label; select.appendChild(option); };

const getStaffFormData = async (apiRequest) => (await apiRequest('/staff/form-data')).data;
const loadActiveEmployees = async (apiRequest) => (await apiRequest('/staff/employees?status=ACTIVE&per_page=50')).data ?? [];
const fillStaffEmployees = (select, employees) => { select.replaceChildren(new Option('Select employee', '')); employees.forEach((employee) => staffOption(select, employee.id, `${employee.employee_code} · ${employee.full_name}`)); };

const initialiseStaffIndex = async (apiRequest, user) => {
    const page = document.getElementById('staffIndexPage');
    if (!page || !staffAllowed(user)) return;
    const error = document.getElementById('staffIndexError');
    const table = document.getElementById('staffEmployeesTable');
    const filters = document.getElementById('staffEmployeeFilters');
    const previous = document.getElementById('staffEmployeesPrev');
    const next = document.getElementById('staffEmployeesNext');
    let currentPage = 1;
    let meta = {};

    const load = async () => {
        staffError(error);
        const query = new URLSearchParams({page: currentPage, per_page: 10, status: document.getElementById('staffEmployeeStatus').value});
        const search = document.getElementById('staffEmployeeSearch').value.trim();
        const departmentId = document.getElementById('staffEmployeeDepartment').value;
        if (search) query.set('search', search);
        if (departmentId) query.set('department_id', departmentId);
        try {
            const [summaryResponse, employeesResponse] = await Promise.all([apiRequest('/staff/summary'), apiRequest(`/staff/employees?${query.toString()}`)]);
            const summary = summaryResponse.data;
            document.getElementById('staffActiveCount').textContent = summary.active_employees;
            document.getElementById('staffClockedInCount').textContent = summary.clocked_in_today;
            document.getElementById('staffPendingLeaveCount').textContent = summary.pending_leave_requests;
            meta = employeesResponse.meta ?? {};
            const employees = employeesResponse.data ?? [];
            table.innerHTML = employees.length ? employees.map((employee) => `<tr class="text-sm text-slate-700"><td class="px-5 py-4"><p class="font-semibold text-slate-950">${staffEscape(employee.full_name)}</p><p class="mt-1 text-xs text-slate-500">${staffEscape(employee.employee_code)}</p></td><td class="px-5 py-4"><p class="font-medium">${staffEscape(employee.job_title)}</p><p class="mt-1 text-xs text-slate-500">${staffEscape(employee.employment_type.replaceAll('_', ' '))}</p></td><td class="px-5 py-4">${staffEscape(employee.department?.name || '—')}</td><td class="px-5 py-4">${staffDate(employee.joined_on)}</td><td class="px-5 py-4">${staffStatus(employee.status)}</td><td class="px-5 py-4"><a href="/staff/employees/${employee.id}/edit" class="font-semibold text-cyan-700 hover:text-cyan-900">Edit</a></td></tr>`).join('') : '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No employee profiles found.</td></tr>';
            document.getElementById('staffEmployeesPagination').textContent = meta.total ? `Showing ${meta.from}–${meta.to} of ${meta.total} employees` : 'No employees found';
            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= (meta.last_page ?? 1);
        } catch (exception) { staffError(error, exception.message); }
    };
    try {
        const formData = await getStaffFormData(apiRequest);
        const departmentSelect = document.getElementById('staffEmployeeDepartment');
        formData.departments.forEach((department) => staffOption(departmentSelect, department.id, `${department.code} · ${department.name}`));
    } catch (exception) { staffError(error, exception.message); }
    filters.addEventListener('submit', (event) => { event.preventDefault(); currentPage = 1; load(); });
    previous.addEventListener('click', () => { if (currentPage > 1) { currentPage -= 1; load(); } });
    next.addEventListener('click', () => { if (currentPage < (meta.last_page ?? 1)) { currentPage += 1; load(); } });
    await load();
};

const initialiseStaffForm = async (apiRequest, user) => {
    const page = document.getElementById('staffEmployeeFormPage');
    if (!page || !staffAllowed(user)) return;
    const error = document.getElementById('staffEmployeeFormError');
    const form = document.getElementById('staffEmployeeForm');
    const employeeId = page.dataset.employeeId;
    const departmentSelect = document.getElementById('staffEmployeeDepartmentInput');
    const userSelect = document.getElementById('staffEmployeeUserInput');
    const setValue = (name, value) => { if (form.elements[name]) form.elements[name].value = value ?? ''; };
    try {
        const formData = await getStaffFormData(apiRequest);
        formData.departments.forEach((department) => staffOption(departmentSelect, department.id, `${department.code} · ${department.name}`));
        formData.users.forEach((account) => staffOption(userSelect, account.id, `${account.name}${account.username ? ` · ${account.username}` : ''}${account.role ? ` (${account.role})` : ''}`));
        if (employeeId) {
            const employee = (await apiRequest(`/staff/employees/${employeeId}`)).data;
            ['first_name', 'last_name', 'email', 'phone', 'job_title', 'employment_type', 'joined_on', 'date_of_birth', 'emergency_contact_name', 'emergency_contact_phone', 'status', 'notes'].forEach((name) => setValue(name, employee[name]));
            setValue('department_id', employee.department?.id);
            setValue('user_id', employee.user?.id);
        } else {
            setValue('joined_on', new Date().toISOString().slice(0, 10));
        }
    } catch (exception) { staffError(error, exception.message); }
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); staffError(error);
        const payload = Object.fromEntries(new FormData(form).entries());
        ['user_id', 'department_id'].forEach((name) => { payload[name] = payload[name] ? Number(payload[name]) : null; });
        ['email', 'phone', 'date_of_birth', 'emergency_contact_name', 'emergency_contact_phone', 'notes'].forEach((name) => { payload[name] = payload[name] || null; });
        try {
            await apiRequest(employeeId ? `/staff/employees/${employeeId}` : '/staff/employees', {method: employeeId ? 'PUT' : 'POST', body: JSON.stringify(payload)});
            window.location.assign('/staff');
        } catch (exception) { staffError(error, exception.message); }
    });
};

const initialiseStaffAttendance = async (apiRequest, user) => {
    const page = document.getElementById('staffAttendancePage');
    if (!page || !staffAllowed(user)) return;
    const error = document.getElementById('staffAttendanceError');
    const form = document.getElementById('staffAttendanceForm');
    const filterForm = document.getElementById('staffAttendanceFilters');
    const table = document.getElementById('staffAttendanceTable');
    const employeeSelect = document.getElementById('staffAttendanceEmployee');
    const load = async () => {
        staffError(error);
        const query = new URLSearchParams({attendance_date: document.getElementById('staffAttendanceFilterDate').value, per_page: 50});
        const status = document.getElementById('staffAttendanceFilterStatus').value;
        if (status) query.set('status', status);
        try {
            const records = (await apiRequest(`/staff/attendance?${query.toString()}`)).data ?? [];
            table.innerHTML = records.length ? records.map((record) => `<tr class="text-sm text-slate-700"><td class="px-5 py-4"><p class="font-semibold text-slate-950">${staffEscape(record.employee?.full_name)}</p><p class="text-xs text-slate-500">${staffEscape(record.employee?.employee_code)}</p></td><td class="px-5 py-4">${staffDate(record.attendance_date)}</td><td class="px-5 py-4">${staffDateTime(record.clock_in)}</td><td class="px-5 py-4">${staffDateTime(record.clock_out)}</td><td class="px-5 py-4">${staffStatus(record.status)}</td><td class="px-5 py-4">${!record.clock_out && !['ABSENT', 'ON_LEAVE'].includes(record.status) ? `<button type="button" data-clock-out="${record.id}" class="font-semibold text-cyan-700 hover:text-cyan-900">Clock out</button>` : '—'}</td></tr>`).join('') : '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No attendance records for this date.</td></tr>';
            table.querySelectorAll('[data-clock-out]').forEach((button) => button.addEventListener('click', async () => { try { await apiRequest(`/staff/attendance/${button.dataset.clockOut}/clock-out`, {method: 'PATCH', body: JSON.stringify({})}); await load(); } catch (exception) { staffError(error, exception.message); } }));
        } catch (exception) { staffError(error, exception.message); }
    };
    try { fillStaffEmployees(employeeSelect, await loadActiveEmployees(apiRequest)); } catch (exception) { staffError(error, exception.message); }
    form.addEventListener('submit', async (event) => { event.preventDefault(); staffError(error); const payload = Object.fromEntries(new FormData(form).entries()); payload.employee_id = Number(payload.employee_id); payload.clock_in = payload.clock_in || null; payload.notes = payload.notes || null; try { await apiRequest('/staff/attendance', {method: 'POST', body: JSON.stringify(payload)}); form.reset(); document.getElementById('staffAttendanceDate').value = new Date().toISOString().slice(0, 10); await load(); } catch (exception) { staffError(error, exception.message); } });
    filterForm.addEventListener('submit', (event) => { event.preventDefault(); load(); });
    await load();
};

const initialiseStaffLeaves = async (apiRequest, user) => {
    const page = document.getElementById('staffLeavesPage');
    if (!page || !staffAllowed(user)) return;
    const error = document.getElementById('staffLeavesError');
    const form = document.getElementById('staffLeaveForm');
    const filterForm = document.getElementById('staffLeaveFilters');
    const employeeSelect = document.getElementById('staffLeaveEmployee');
    const table = document.getElementById('staffLeavesTable');
    const load = async () => {
        staffError(error);
        const query = new URLSearchParams({per_page: 50});
        const status = document.getElementById('staffLeaveStatus').value;
        const from = document.getElementById('staffLeaveFromDate').value;
        if (status) query.set('status', status); if (from) query.set('from_date', from);
        try {
            const leaves = (await apiRequest(`/staff/leave-requests?${query.toString()}`)).data ?? [];
            table.innerHTML = leaves.length ? leaves.map((leave) => `<tr class="text-sm text-slate-700"><td class="px-5 py-4"><p class="font-semibold text-slate-950">${staffEscape(leave.leave_code)}</p><p class="mt-1 text-xs text-slate-500">${staffDateTime(leave.requested_at)}</p></td><td class="px-5 py-4"><p class="font-medium">${staffEscape(leave.employee?.full_name)}</p><p class="mt-1 text-xs text-slate-500">${staffEscape(leave.employee?.department || '')}</p></td><td class="px-5 py-4">${staffDate(leave.start_date)} – ${staffDate(leave.end_date)}<p class="mt-1 text-xs text-slate-500">${leave.total_days} day(s)</p></td><td class="px-5 py-4">${staffEscape(leave.leave_type)}</td><td class="px-5 py-4">${staffStatus(leave.status)}</td><td class="px-5 py-4">${leave.status === 'PENDING' ? `<div class="flex gap-3"><button type="button" data-review="APPROVED" data-leave-id="${leave.id}" class="font-semibold text-emerald-700">Approve</button><button type="button" data-review="REJECTED" data-leave-id="${leave.id}" class="font-semibold text-red-700">Reject</button></div>` : staffEscape(leave.reviewed_by || '—')}</td></tr>`).join('') : '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No leave requests found.</td></tr>';
            table.querySelectorAll('[data-review]').forEach((button) => button.addEventListener('click', async () => { const comment = window.prompt(`Optional comment for ${button.dataset.review.toLowerCase()} decision:`); if (comment === null) return; try { await apiRequest(`/staff/leave-requests/${button.dataset.leaveId}/review`, {method: 'PATCH', body: JSON.stringify({decision: button.dataset.review, reviewer_comment: comment || null})}); await load(); } catch (exception) { staffError(error, exception.message); } }));
        } catch (exception) { staffError(error, exception.message); }
    };
    try { fillStaffEmployees(employeeSelect, await loadActiveEmployees(apiRequest)); } catch (exception) { staffError(error, exception.message); }
    form.addEventListener('submit', async (event) => { event.preventDefault(); staffError(error); const payload = Object.fromEntries(new FormData(form).entries()); payload.employee_id = Number(payload.employee_id); try { await apiRequest('/staff/leave-requests', {method: 'POST', body: JSON.stringify(payload)}); form.reset(); await load(); } catch (exception) { staffError(error, exception.message); } });
    filterForm.addEventListener('submit', (event) => { event.preventDefault(); load(); });
    await load();
};

export const initialiseStaffPages = async (apiRequest, user) => {
    await initialiseStaffIndex(apiRequest, user);
    await initialiseStaffForm(apiRequest, user);
    await initialiseStaffAttendance(apiRequest, user);
    await initialiseStaffLeaves(apiRequest, user);
};
