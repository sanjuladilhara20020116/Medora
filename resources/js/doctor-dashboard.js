const doctorDashboardEscape = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
}[character]));

const doctorDashboardDate = (value) => value ? new Date(`${value}T00:00:00`).toLocaleDateString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric',
}) : '—';

const doctorDashboardDateTime = (value) => value ? new Date(value).toLocaleString('en-GB', {
    dateStyle: 'medium', timeStyle: 'short',
}) : '—';

const doctorDashboardSetText = (id, value) => {
    const element = document.getElementById(id);
    if (element) element.textContent = value ?? '—';
};

const doctorDashboardError = (message = '') => {
    const element = document.getElementById('doctorDashboardError');
    if (!element) return;
    element.textContent = message;
    element.classList.toggle('hidden', !message);
};

const doctorDashboardEmpty = (message) => `<p class="px-5 py-8 text-sm text-slate-500">${doctorDashboardEscape(message)}</p>`;

const renderUpcomingAppointments = (appointments = []) => {
    const container = document.getElementById('doctorUpcomingAppointments');
    if (!container) return;
    container.innerHTML = appointments.length ? appointments.map((appointment) => `<div class="flex items-start justify-between gap-4 p-5"><div><a href="/patients/${appointment.patient?.id}" class="font-semibold text-slate-950 hover:text-cyan-700">${doctorDashboardEscape(appointment.patient?.full_name || 'Patient')}</a><p class="mt-1 text-sm text-slate-500">${doctorDashboardEscape(appointment.patient?.patient_code)} · ${doctorDashboardEscape(appointment.department || 'No department')}</p></div><div class="text-right"><p class="text-sm font-semibold text-slate-900">${doctorDashboardDate(appointment.appointment_date)} · ${doctorDashboardEscape(appointment.start_time)}</p><p class="mt-1 text-xs font-medium text-cyan-700">${doctorDashboardEscape(String(appointment.status).replaceAll('_', ' '))}</p></div></div>`).join('') : doctorDashboardEmpty('No upcoming appointments are scheduled.');
};

const renderRecentRecords = (records = []) => {
    const container = document.getElementById('doctorRecentRecords');
    if (!container) return;
    container.innerHTML = records.length ? records.map((record) => `<a href="/medical-records/${record.id}" class="block p-5 hover:bg-slate-50"><p class="font-semibold text-slate-950">${doctorDashboardEscape(record.patient?.full_name || 'Patient')}</p><p class="mt-1 text-sm text-slate-600">${doctorDashboardEscape(record.diagnosis || 'No diagnosis recorded')}</p><p class="mt-2 text-xs text-slate-400">${doctorDashboardEscape(record.record_code)} · ${doctorDashboardDateTime(record.recorded_at)}</p></a>`).join('') : doctorDashboardEmpty('No medical records have been created under your profile.');
};

const fillDoctorProfile = (doctor) => {
    const form = document.getElementById('doctorProfileForm');
    if (!form) return;
    ['name', 'username', 'email', 'phone'].forEach((name) => {
        form.elements[name].value = doctor.user?.[name] ?? '';
    });
};

export const initialiseDoctorDashboard = async (apiRequest, user) => {
    const page = document.getElementById('doctorDashboardPage');
    if (!page) return;
    if (user?.role?.slug !== 'DOCTOR') {
        doctorDashboardError('This dashboard is available to doctor accounts only.');
        return;
    }

    const form = document.getElementById('doctorProfileForm');
    const saveButton = document.getElementById('doctorProfileSaveButton');
    const load = async () => {
        doctorDashboardError();
        try {
            const [dashboardResponse, profileResponse] = await Promise.all([
                apiRequest('/doctor-portal/dashboard'),
                apiRequest('/doctor-portal/profile'),
            ]);
            const dashboard = dashboardResponse.data;
            doctorDashboardSetText('doctorDashboardGreeting', `Welcome, ${dashboard.doctor.user?.name || 'Doctor'}`);
            doctorDashboardSetText('doctorDashboardSubtitle', `${dashboard.doctor.doctor_code || 'Doctor profile'} · ${dashboard.doctor.specialization || 'Clinical workspace'}`);
            doctorDashboardSetText('doctorTodayAppointments', dashboard.statistics.appointments_today);
            doctorDashboardSetText('doctorPendingAppointments', dashboard.statistics.pending_today);
            doctorDashboardSetText('doctorMedicalRecordCount', dashboard.statistics.medical_records);
            doctorDashboardSetText('doctorPatientsTreated', dashboard.statistics.patients_treated);
            renderUpcomingAppointments(dashboard.upcoming_appointments);
            renderRecentRecords(dashboard.recent_medical_records);
            fillDoctorProfile(profileResponse.data);
        } catch (exception) {
            doctorDashboardError(exception.message ?? 'Doctor dashboard data could not be loaded.');
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        doctorDashboardError();
        const payload = Object.fromEntries(new FormData(form).entries());
        if (!payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
            delete payload.current_password;
        }
        if (!payload.phone) payload.phone = null;
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
        try {
            const response = await apiRequest('/doctor-portal/profile', {method: 'PATCH', body: JSON.stringify(payload)});
            fillDoctorProfile(response.data);
            form.elements.current_password.value = '';
            form.elements.password.value = '';
            form.elements.password_confirmation.value = '';
            const currentUserName = document.getElementById('currentUserName');
            if (currentUserName) currentUserName.textContent = response.data.user?.name ?? currentUserName.textContent;
        } catch (exception) {
            doctorDashboardError(exception.message ?? 'Profile details could not be saved.');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Profile Details';
        }
    });

    await load();
};
