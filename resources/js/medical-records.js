const canManageRecords = (user) =>
    ['ADMIN', 'DOCTOR'].includes(user.role?.slug);

const setText = (id, value) => {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value || '—';
    }
};

const formatDateTime = (value) => value
    ? new Date(value).toLocaleString()
    : '—';

const toDateTimeInputValue = (value) => {
    const date = value ? new Date(value) : new Date();
    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);

    return offsetDate.toISOString().slice(0, 16);
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const populateSelect = (select, items, label) => {
    if (!select) {
        return;
    }

    select.replaceChildren(select.options[0]);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = label(item);
        select.appendChild(option);
    });
};

const initialiseMedicalRecordIndex = async (apiRequest, user) => {
    const root = document.getElementById('medicalRecordsIndexPage');

    if (!root) {
        return;
    }

    const errorBox = document.getElementById('medicalRecordsError');
    const filters = document.getElementById('medicalRecordFilters');
    const search = document.getElementById('medicalRecordSearch');
    const patientFilter = document.getElementById('medicalRecordPatientFilter');
    const doctorFilter = document.getElementById('medicalRecordDoctorFilter');
    const dateFilter = document.getElementById('medicalRecordDateFilter');
    const tableBody = document.getElementById('medicalRecordsTableBody');
    const pageInfo = document.getElementById('medicalRecordsPaginationInfo');
    const previous = document.getElementById('medicalRecordsPrevPage');
    const next = document.getElementById('medicalRecordsNextPage');
    const createLink = document.getElementById('createMedicalRecordLink');
    let currentPage = 1;
    let lastPage = 1;

    if (!canManageRecords(user)) {
        createLink?.classList.add('hidden');
    }

    if (user.role?.slug === 'DOCTOR') {
        doctorFilter.closest('select')?.classList.add('hidden');
    }

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const renderRows = (records) => {
        tableBody.replaceChildren();

        if (records.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No medical records found.</td>';
            tableBody.appendChild(row);
            return;
        }

        records.forEach((record) => {
            const row = document.createElement('tr');
            row.className = 'text-sm text-slate-700';
            row.innerHTML = `
                <td class="px-5 py-4 font-semibold text-cyan-700">${escapeHtml(record.record_code)}</td>
                <td class="px-5 py-4"><p class="font-semibold text-slate-900">${escapeHtml(record.patient?.full_name)}</p><p class="mt-1 text-xs text-slate-500">${escapeHtml(record.patient?.patient_code)}</p></td>
                <td class="px-5 py-4 max-w-xs truncate">${escapeHtml(record.diagnosis)}</td>
                <td class="px-5 py-4">${escapeHtml(record.doctor?.name)}</td>
                <td class="px-5 py-4">${escapeHtml(formatDateTime(record.recorded_at))}</td>
                <td class="px-5 py-4"><a href="/medical-records/${record.id}" class="font-semibold text-cyan-700 hover:text-cyan-900">View</a></td>
            `;
            tableBody.appendChild(row);
        });
    };

    const loadRecords = async () => {
        const query = new URLSearchParams({ per_page: '10', page: String(currentPage) });

        if (search.value.trim()) query.set('search', search.value.trim());
        if (patientFilter.value) query.set('patient_id', patientFilter.value);
        if (doctorFilter.value) query.set('doctor_id', doctorFilter.value);
        if (dateFilter.value) query.set('recorded_on', dateFilter.value);

        try {
            const response = await apiRequest(`/medical-records?${query}`);
            renderRows(response.data ?? []);
            lastPage = response.meta?.last_page ?? 1;
            currentPage = response.meta?.current_page ?? 1;
            pageInfo.textContent = `Showing page ${currentPage} of ${lastPage} (${response.meta?.total ?? 0} records)`;
            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= lastPage;
            errorBox.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };

    try {
        const [patients, doctors] = await Promise.all([
            apiRequest('/patients?status=active&per_page=50'),
            user.role?.slug === 'DOCTOR'
                ? Promise.resolve({ data: [] })
                : apiRequest('/doctors?status=active&per_page=50'),
        ]);

        populateSelect(patientFilter, patients.data ?? [], (patient) => `${patient.patient_code} - ${patient.full_name}`);
        populateSelect(doctorFilter, doctors.data ?? [], (doctor) => doctor.user?.name ?? doctor.doctor_code);
        await loadRecords();
    } catch (error) {
        showError(error.message);
    }

    filters.addEventListener('submit', async (event) => {
        event.preventDefault();
        currentPage = 1;
        await loadRecords();
    });
    previous.addEventListener('click', async () => {
        if (currentPage > 1) {
            currentPage -= 1;
            await loadRecords();
        }
    });
    next.addEventListener('click', async () => {
        if (currentPage < lastPage) {
            currentPage += 1;
            await loadRecords();
        }
    });
};

const initialiseMedicalRecordForm = async (apiRequest, user) => {
    const root = document.getElementById('medicalRecordFormPage');

    if (!root) {
        return;
    }

    const form = document.getElementById('medicalRecordForm');
    const errorBox = document.getElementById('medicalRecordFormError');
    const saveButton = document.getElementById('medicalRecordSaveButton');
    const patientSelect = document.getElementById('medicalRecordPatient');
    const doctorField = document.getElementById('medicalRecordDoctorField');
    const doctorSelect = document.getElementById('medicalRecordDoctor');
    const appointmentSelect = document.getElementById('medicalRecordAppointment');
    const recordId = root.dataset.medicalRecordId;
    const editing = Boolean(recordId);

    if (!canManageRecords(user)) {
        form.classList.add('hidden');
        errorBox.textContent = 'Only administrators and doctors can create or update medical records.';
        errorBox.classList.remove('hidden');
        return;
    }

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const loadAppointments = async (selectedAppointmentId = '') => {
        appointmentSelect.replaceChildren();
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = patientSelect.value ? 'No related appointment' : 'Select a patient first';
        appointmentSelect.appendChild(defaultOption);

        if (!patientSelect.value) {
            appointmentSelect.disabled = true;
            return;
        }

        appointmentSelect.disabled = false;
        const response = await apiRequest(`/appointments?patient_id=${patientSelect.value}&per_page=50`);
        const available = (response.data ?? []).filter((appointment) =>
            ['IN_PROGRESS', 'COMPLETED'].includes(appointment.status)
            || String(appointment.id) === String(selectedAppointmentId)
        );

        available.forEach((appointment) => {
            const option = document.createElement('option');
            option.value = appointment.id;
            option.textContent = `${appointment.appointment_code} - ${appointment.appointment_date} (${appointment.status.replaceAll('_', ' ')})`;
            appointmentSelect.appendChild(option);
        });

        appointmentSelect.value = selectedAppointmentId;
    };

    try {
        const requests = [apiRequest('/patients?status=active&per_page=50')];
        if (user.role?.slug === 'ADMIN') requests.push(apiRequest('/doctors?status=active&per_page=50'));
        const [patients, doctors] = await Promise.all(requests);
        populateSelect(patientSelect, patients.data ?? [], (patient) => `${patient.patient_code} - ${patient.full_name}`);

        if (user.role?.slug === 'ADMIN') {
            populateSelect(doctorSelect, doctors.data ?? [], (doctor) => doctor.user?.name ?? doctor.doctor_code);
            doctorSelect.required = true;
        } else {
            doctorField.classList.add('hidden');
            doctorSelect.disabled = true;
        }

        document.getElementById('medicalRecordRecordedAt').value = toDateTimeInputValue();

        if (editing) {
            const response = await apiRequest(`/medical-records/${recordId}`);
            const record = response.data;
            patientSelect.value = String(record.patient.id);
            doctorSelect.value = record.doctor?.id ? String(record.doctor.id) : '';
            document.getElementById('medicalRecordRecordedAt').value = toDateTimeInputValue(record.recorded_at);
            form.elements.chief_complaint.value = record.chief_complaint ?? '';
            form.elements.diagnosis.value = record.diagnosis ?? '';
            form.elements.treatment_plan.value = record.treatment_plan ?? '';
            form.elements.clinical_notes.value = record.clinical_notes ?? '';
            form.elements.follow_up_date.value = record.follow_up_date ?? '';
            form.elements.follow_up_notes.value = record.follow_up_notes ?? '';
            await loadAppointments(record.appointment?.id ?? '');
            patientSelect.disabled = true;
            doctorSelect.disabled = true;
            appointmentSelect.disabled = true;
        }
    } catch (error) {
        showError(error.message);
    }

    patientSelect.addEventListener('change', () => loadAppointments().catch((error) => showError(error.message)));

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.classList.add('hidden');
        saveButton.disabled = true;
        saveButton.textContent = editing ? 'Updating...' : 'Saving...';

        const payload = Object.fromEntries(new FormData(form).entries());
        ['patient_id', 'doctor_id', 'appointment_id'].forEach((key) => {
            if (payload[key] === '') payload[key] = null;
            else if (payload[key] !== undefined) payload[key] = Number(payload[key]);
        });
        Object.keys(payload).forEach((key) => {
            if (payload[key] === '') payload[key] = null;
        });

        try {
            const response = await apiRequest(editing ? `/medical-records/${recordId}` : '/medical-records', {
                method: editing ? 'PUT' : 'POST',
                body: JSON.stringify(payload),
            });
            window.location.href = `/medical-records/${response.data.id}`;
        } catch (error) {
            showError(error.message);
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Medical Record';
        }
    });
};

const initialiseMedicalRecordShow = async (apiRequest, user) => {
    const root = document.getElementById('medicalRecordShowPage');

    if (!root) {
        return;
    }

    const recordId = root.dataset.medicalRecordId;
    const errorBox = document.getElementById('medicalRecordShowError');
    const editLink = document.getElementById('medicalRecordEditLink');
    const prescriptionForm = document.getElementById('prescriptionForm');
    const prescriptionSummary = document.getElementById('prescriptionSummary');
    const prescriptionItems = document.getElementById('prescriptionItems');
    const prescriptionButton = document.getElementById('editPrescriptionButton');
    const reportForm = document.getElementById('medicalReportForm');
    const reportButton = document.getElementById('showMedicalReportFormButton');
    const reportsList = document.getElementById('medicalReportsList');
    let currentRecord;
    let treatmentHistory = [];

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const addPrescriptionItem = (item = {}) => {
        const row = document.createElement('div');
        row.className = 'prescription-item grid gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-3';
        row.innerHTML = `
            <input data-field="medicine_name" required placeholder="Medicine name" class="rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.medicine_name)}">
            <input data-field="dosage" required placeholder="Dosage (e.g. 500 mg)" class="rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.dosage)}">
            <input data-field="frequency" required placeholder="Frequency (e.g. Twice daily)" class="rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.frequency)}">
            <input data-field="duration_days" type="number" min="1" placeholder="Duration (days)" class="rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.duration_days)}">
            <input data-field="quantity" type="number" min="0.01" step="0.01" placeholder="Quantity" class="rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.quantity)}">
            <div class="flex gap-3"><input data-field="instructions" placeholder="Instructions" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2" value="${escapeHtml(item.instructions)}"><button type="button" class="remove-prescription-item rounded-lg px-3 text-sm font-semibold text-red-600">Remove</button></div>
        `;
        row.querySelector('.remove-prescription-item').addEventListener('click', () => row.remove());
        prescriptionItems.appendChild(row);
    };

    const renderPrescription = () => {
        const prescription = currentRecord.prescription;

        if (!prescription) {
            prescriptionSummary.textContent = 'No prescription has been recorded.';
            return;
        }

        const items = prescription.items.map((item) => `
            <tr class="border-t border-slate-100 text-sm text-slate-700">
                <td class="px-3 py-3 font-semibold text-slate-900">${escapeHtml(item.medicine_name)}</td>
                <td class="px-3 py-3">${escapeHtml(item.dosage)}</td>
                <td class="px-3 py-3">${escapeHtml(item.frequency)}</td>
                <td class="px-3 py-3">${escapeHtml(item.duration_days ? `${item.duration_days} days` : '—')}</td>
                <td class="px-3 py-3">${escapeHtml(item.instructions || '—')}</td>
            </tr>`).join('');
        prescriptionSummary.innerHTML = `
            <p class="mb-3 text-sm text-slate-500">${escapeHtml(prescription.prescription_code)} issued ${escapeHtml(formatDateTime(prescription.issued_at))}</p>
            <div class="overflow-x-auto"><table class="min-w-full"><thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-3 py-3">Medicine</th><th class="px-3 py-3">Dosage</th><th class="px-3 py-3">Frequency</th><th class="px-3 py-3">Duration</th><th class="px-3 py-3">Instructions</th></tr></thead><tbody>${items}</tbody></table></div>
            ${prescription.notes ? `<p class="mt-4 whitespace-pre-wrap text-sm text-slate-600">${escapeHtml(prescription.notes)}</p>` : ''}`;
    };

    const renderReports = () => {
        reportsList.replaceChildren();
        const reports = currentRecord.reports ?? [];

        if (reports.length === 0) {
            reportsList.textContent = 'No medical reports have been uploaded.';
            reportsList.className = 'mt-5 text-sm text-slate-500';
            return;
        }

        reportsList.className = 'mt-5 space-y-3';
        reports.forEach((report) => {
            const item = document.createElement('div');
            item.className = 'flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between';
            item.innerHTML = `<div><p class="font-semibold text-slate-900">${escapeHtml(report.title)}</p><p class="mt-1 text-sm text-slate-500">${escapeHtml(report.report_type)} · ${escapeHtml(report.file_name)} · ${escapeHtml(new Intl.NumberFormat().format(report.file_size))} bytes</p>${report.notes ? `<p class="mt-2 text-sm text-slate-600">${escapeHtml(report.notes)}</p>` : ''}</div><div class="flex shrink-0 gap-3"></div>`;
            const actions = item.querySelector('div:last-child');
            const download = document.createElement('button');
            download.type = 'button';
            download.className = 'rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold';
            download.textContent = 'Download';
            download.addEventListener('click', () => downloadReport(report));
            actions.appendChild(download);

            if (canManageRecords(user)) {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600';
                remove.textContent = 'Delete';
                remove.addEventListener('click', () => deleteReport(report));
                actions.appendChild(remove);
            }
            reportsList.appendChild(item);
        });
    };

    const renderTreatmentHistory = () => {
        const container = document.getElementById('treatmentHistory');
        container.replaceChildren();

        if (treatmentHistory.length === 0) {
            container.textContent = 'No treatment history is available for this patient.';
            container.className = 'mt-5 text-sm text-slate-500';
            return;
        }

        container.className = 'mt-5 space-y-3';
        treatmentHistory.forEach((record) => {
            const entry = document.createElement('a');
            entry.href = `/medical-records/${record.id}`;
            entry.className = 'block rounded-xl border border-slate-200 p-4 hover:border-cyan-300 hover:bg-cyan-50/30';
            entry.innerHTML = `<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-slate-900">${escapeHtml(record.diagnosis)}</p><p class="mt-1 text-sm text-slate-500">${escapeHtml(record.record_code)} · ${escapeHtml(formatDateTime(record.recorded_at))}</p></div><span class="text-sm font-semibold text-cyan-700">View record</span></div>`;
            container.appendChild(entry);
        });
    };

    const downloadReport = async (report) => {
        try {
            const response = await fetch(`/api${report.download_endpoint}`, {
                headers: {
                    Accept: 'application/octet-stream',
                    Authorization: `Bearer ${sessionStorage.getItem('medora_access_token')}`,
                },
            });
            if (!response.ok) throw new Error('The medical report could not be downloaded.');
            const url = URL.createObjectURL(await response.blob());
            const link = document.createElement('a');
            link.href = url;
            link.download = report.file_name;
            link.click();
            URL.revokeObjectURL(url);
        } catch (error) {
            showError(error.message);
        }
    };

    const deleteReport = async (report) => {
        if (!window.confirm(`Delete ${report.title}? This cannot be undone.`)) return;
        try {
            await apiRequest(`/medical-records/${recordId}/reports/${report.id}`, { method: 'DELETE' });
            await loadRecord();
        } catch (error) {
            showError(error.message);
        }
    };

    const renderRecord = () => {
        setText('medicalRecordCode', currentRecord.record_code);
        setText('medicalRecordPatientName', currentRecord.patient?.full_name);
        setText('medicalRecordPatientSummary', [currentRecord.patient?.patient_code, currentRecord.patient?.age ? `${currentRecord.patient.age} years` : '', currentRecord.patient?.blood_group].filter(Boolean).join(' · '));
        setText('medicalRecordDoctor', currentRecord.doctor?.name);
        setText('medicalRecordRecordedAt', formatDateTime(currentRecord.recorded_at));
        setText('medicalRecordAppointment', currentRecord.appointment ? `${currentRecord.appointment.appointment_code} · ${currentRecord.appointment.status.replaceAll('_', ' ')}` : 'Not linked');
        setText('medicalRecordFollowUp', currentRecord.follow_up_date || 'Not scheduled');
        setText('medicalRecordComplaint', currentRecord.chief_complaint);
        setText('medicalRecordDiagnosis', currentRecord.diagnosis);
        setText('medicalRecordTreatmentPlan', currentRecord.treatment_plan);
        setText('medicalRecordClinicalNotes', currentRecord.clinical_notes);
        setText('medicalRecordAllergies', currentRecord.patient?.allergies);
        setText('medicalRecordConditions', currentRecord.patient?.chronic_conditions);
        editLink.href = `/medical-records/${recordId}/edit`;
        editLink.classList.toggle('hidden', !canManageRecords(user));
        prescriptionButton.classList.toggle('hidden', !canManageRecords(user));
        reportButton.classList.toggle('hidden', !canManageRecords(user));
        renderPrescription();
        renderReports();
    };

    const loadRecord = async () => {
        try {
            const response = await apiRequest(`/medical-records/${recordId}`);
            currentRecord = response.data;
            const historyResponse = await apiRequest(`/medical-records?patient_id=${currentRecord.patient.id}&per_page=50`);
            treatmentHistory = historyResponse.data ?? [];
            renderRecord();
            renderTreatmentHistory();
            errorBox.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };

    prescriptionButton.addEventListener('click', () => {
        prescriptionItems.replaceChildren();
        const prescription = currentRecord.prescription;
        document.getElementById('prescriptionIssuedAt').value = toDateTimeInputValue(prescription?.issued_at || currentRecord.recorded_at);
        prescriptionForm.elements.notes.value = prescription?.notes ?? '';
        (prescription?.items?.length ? prescription.items : [{}]).forEach(addPrescriptionItem);
        prescriptionForm.classList.remove('hidden');
        prescriptionButton.classList.add('hidden');
    });
    document.getElementById('cancelPrescriptionButton').addEventListener('click', () => {
        prescriptionForm.classList.add('hidden');
        if (canManageRecords(user)) prescriptionButton.classList.remove('hidden');
    });
    document.getElementById('addPrescriptionItem').addEventListener('click', () => addPrescriptionItem());
    prescriptionForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const items = [...prescriptionItems.querySelectorAll('.prescription-item')].map((row) => {
            const value = (field) => row.querySelector(`[data-field="${field}"]`).value.trim();
            return {
                medicine_name: value('medicine_name'),
                dosage: value('dosage'),
                frequency: value('frequency'),
                duration_days: value('duration_days') ? Number(value('duration_days')) : null,
                quantity: value('quantity') ? Number(value('quantity')) : null,
                instructions: value('instructions') || null,
            };
        });
        try {
            const response = await apiRequest(`/medical-records/${recordId}/prescription`, {
                method: 'POST',
                body: JSON.stringify({ issued_at: prescriptionForm.elements.issued_at.value, notes: prescriptionForm.elements.notes.value || null, items }),
            });
            currentRecord = response.data;
            prescriptionForm.classList.add('hidden');
            prescriptionButton.classList.remove('hidden');
            renderPrescription();
        } catch (error) {
            showError(error.message);
        }
    });

    reportButton.addEventListener('click', () => {
        reportForm.classList.remove('hidden');
        reportButton.classList.add('hidden');
    });
    document.getElementById('cancelMedicalReportButton').addEventListener('click', () => {
        reportForm.reset();
        reportForm.classList.add('hidden');
        if (canManageRecords(user)) reportButton.classList.remove('hidden');
    });
    reportForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await apiRequest(`/medical-records/${recordId}/reports`, { method: 'POST', body: new FormData(reportForm) });
            reportForm.reset();
            reportForm.classList.add('hidden');
            reportButton.classList.remove('hidden');
            await loadRecord();
        } catch (error) {
            showError(error.message);
        }
    });

    await loadRecord();
};

export const initialiseMedicalRecordPages = async (apiRequest, user) => {
    await initialiseMedicalRecordIndex(apiRequest, user);
    await initialiseMedicalRecordForm(apiRequest, user);
    await initialiseMedicalRecordShow(apiRequest, user);
};
