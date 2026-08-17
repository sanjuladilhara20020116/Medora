const canRequestLabTests = (user) => ['ADMIN', 'DOCTOR'].includes(user.role?.slug);
const canOperateLaboratory = (user) => ['ADMIN', 'LAB_STAFF'].includes(user.role?.slug);
const isAdministrator = (user) => user.role?.slug === 'ADMIN';

const laboratoryEscapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const laboratoryFormatDateTime = (value) => value
    ? new Date(value).toLocaleString()
    : '—';

const laboratoryDateTimeInputValue = (value) => {
    const date = value ? new Date(value) : new Date();
    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);

    return localDate.toISOString().slice(0, 16);
};

const setLaboratoryText = (id, value) => {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value || '—';
    }
};

const populateLaboratorySelect = (select, items, label) => {
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

const statusLabel = (status) => (status || '—').replaceAll('_', ' ');

const initialiseLaboratoryIndex = async (apiRequest, user) => {
    const root = document.getElementById('laboratoryIndexPage');

    if (!root) {
        return;
    }

    const errorBox = document.getElementById('laboratoryError');
    const form = document.getElementById('laboratoryFilters');
    const search = document.getElementById('laboratorySearch');
    const status = document.getElementById('laboratoryStatusFilter');
    const patient = document.getElementById('laboratoryPatientFilter');
    const date = document.getElementById('laboratoryDateFilter');
    const tableBody = document.getElementById('laboratoryTableBody');
    const pageInfo = document.getElementById('laboratoryPaginationInfo');
    const previous = document.getElementById('laboratoryPrevPage');
    const next = document.getElementById('laboratoryNextPage');
    const createLink = document.getElementById('createLabRequestLink');
    const testsLink = document.getElementById('laboratoryTestsLink');
    let currentPage = 1;
    let lastPage = 1;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    if (!canRequestLabTests(user)) createLink.classList.add('hidden');
    if (!isAdministrator(user) && user.role?.slug === 'DOCTOR') testsLink.classList.add('hidden');

    const renderRequests = (requests) => {
        tableBody.replaceChildren();

        if (requests.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No laboratory requests found.</td>';
            tableBody.appendChild(row);
            return;
        }

        requests.forEach((request) => {
            const row = document.createElement('tr');
            row.className = 'text-sm text-slate-700';
            row.innerHTML = `
                <td class="px-5 py-4"><p class="font-semibold text-cyan-700">${laboratoryEscapeHtml(request.request_code)}</p><p class="mt-1 text-xs text-slate-500">${laboratoryEscapeHtml(laboratoryFormatDateTime(request.requested_at))}</p></td>
                <td class="px-5 py-4"><p class="font-semibold text-slate-900">${laboratoryEscapeHtml(request.patient?.full_name)}</p><p class="mt-1 text-xs text-slate-500">${laboratoryEscapeHtml(request.patient?.patient_code)}</p></td>
                <td class="px-5 py-4"><p class="font-semibold text-slate-900">${laboratoryEscapeHtml(request.lab_test?.name)}</p><p class="mt-1 text-xs text-slate-500">${laboratoryEscapeHtml(request.lab_test?.specimen_type)}</p></td>
                <td class="px-5 py-4">${laboratoryEscapeHtml(request.doctor?.name)}</td>
                <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${laboratoryEscapeHtml(statusLabel(request.status))}</span></td>
                <td class="px-5 py-4"><a href="/laboratory/requests/${request.id}" class="font-semibold text-cyan-700 hover:text-cyan-900">View</a></td>
            `;
            tableBody.appendChild(row);
        });
    };

    const loadRequests = async () => {
        const query = new URLSearchParams({ per_page: '10', page: String(currentPage) });
        if (search.value.trim()) query.set('search', search.value.trim());
        if (status.value) query.set('status', status.value);
        if (patient.value) query.set('patient_id', patient.value);
        if (date.value) query.set('requested_on', date.value);

        try {
            const response = await apiRequest(`/lab-requests?${query}`);
            renderRequests(response.data ?? []);
            currentPage = response.meta?.current_page ?? 1;
            lastPage = response.meta?.last_page ?? 1;
            pageInfo.textContent = `Showing page ${currentPage} of ${lastPage} (${response.meta?.total ?? 0} requests)`;
            previous.disabled = currentPage <= 1;
            next.disabled = currentPage >= lastPage;
            errorBox.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };

    try {
        if (user.role?.slug === 'LAB_STAFF') {
            patient.classList.add('hidden');
        } else {
            const patients = await apiRequest('/patients?status=active&per_page=50');
            populateLaboratorySelect(patient, patients.data ?? [], (item) => `${item.patient_code} - ${item.full_name}`);
        }
        await loadRequests();
    } catch (error) {
        showError(error.message);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        currentPage = 1;
        await loadRequests();
    });
    previous.addEventListener('click', async () => {
        if (currentPage > 1) {
            currentPage -= 1;
            await loadRequests();
        }
    });
    next.addEventListener('click', async () => {
        if (currentPage < lastPage) {
            currentPage += 1;
            await loadRequests();
        }
    });
};

const initialiseLabRequestForm = async (apiRequest, user) => {
    const root = document.getElementById('labRequestFormPage');

    if (!root) {
        return;
    }

    const form = document.getElementById('labRequestForm');
    const errorBox = document.getElementById('labRequestFormError');
    const saveButton = document.getElementById('labRequestSaveButton');
    const patient = document.getElementById('labRequestPatient');
    const doctorField = document.getElementById('labRequestDoctorField');
    const doctor = document.getElementById('labRequestDoctor');
    const test = document.getElementById('labRequestTest');
    const medicalRecord = document.getElementById('labRequestMedicalRecord');
    const testHint = document.getElementById('labTestHint');
    let tests = [];

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    if (!canRequestLabTests(user)) {
        form.classList.add('hidden');
        showError('Only administrators and doctors can create laboratory test requests.');
        return;
    }

    const loadMedicalRecords = async () => {
        medicalRecord.replaceChildren();
        const blank = document.createElement('option');
        blank.value = '';
        blank.textContent = patient.value ? 'No related medical record' : 'Select a patient first';
        medicalRecord.appendChild(blank);

        if (!patient.value) {
            medicalRecord.disabled = true;
            return;
        }

        medicalRecord.disabled = false;
        const response = await apiRequest(`/medical-records?patient_id=${patient.value}&per_page=50`);
        const records = (response.data ?? []).filter((record) =>
            user.role?.slug !== 'ADMIN' || !doctor.value || String(record.doctor?.id) === String(doctor.value)
        );
        records.forEach((record) => {
            const option = document.createElement('option');
            option.value = record.id;
            option.textContent = `${record.record_code} - ${record.diagnosis}`;
            medicalRecord.appendChild(option);
        });
    };

    try {
        const requests = [
            apiRequest('/patients?status=active&per_page=50'),
            apiRequest('/lab-tests?status=active&per_page=50'),
        ];
        if (isAdministrator(user)) requests.push(apiRequest('/doctors?status=active&per_page=50'));
        const [patients, testResponse, doctors] = await Promise.all(requests);
        tests = testResponse.data ?? [];
        populateLaboratorySelect(patient, patients.data ?? [], (item) => `${item.patient_code} - ${item.full_name}`);
        populateLaboratorySelect(test, tests, (item) => `${item.test_code} - ${item.name}`);

        if (isAdministrator(user)) {
            populateLaboratorySelect(doctor, doctors.data ?? [], (item) => item.user?.name ?? item.doctor_code);
            doctor.required = true;
        } else {
            doctorField.classList.add('hidden');
            doctor.disabled = true;
        }
    } catch (error) {
        showError(error.message);
    }

    patient.addEventListener('change', () => loadMedicalRecords().catch((error) => showError(error.message)));
    doctor.addEventListener('change', () => loadMedicalRecords().catch((error) => showError(error.message)));
    test.addEventListener('change', () => {
        const selected = tests.find((item) => String(item.id) === test.value);
        testHint.textContent = selected
            ? `Specimen: ${selected.specimen_type}. Reference range: ${selected.reference_range || 'Not specified'}. Turnaround: ${selected.turnaround_hours ? `${selected.turnaround_hours} hours` : 'Not specified'}.`
            : 'Select a test to view specimen requirements.';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.classList.add('hidden');
        saveButton.disabled = true;
        saveButton.textContent = 'Creating...';
        const payload = Object.fromEntries(new FormData(form).entries());
        ['patient_id', 'doctor_id', 'lab_test_id', 'medical_record_id'].forEach((key) => {
            if (payload[key] === '') payload[key] = null;
            else if (payload[key] !== undefined) payload[key] = Number(payload[key]);
        });
        if (payload.clinical_notes === '') payload.clinical_notes = null;

        try {
            const response = await apiRequest('/lab-requests', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            window.location.href = `/laboratory/requests/${response.data.id}`;
        } catch (error) {
            showError(error.message);
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Create Test Request';
        }
    });
};

const initialiseLabRequestShow = async (apiRequest, user) => {
    const root = document.getElementById('labRequestShowPage');

    if (!root) {
        return;
    }

    const requestId = root.dataset.labRequestId;
    const errorBox = document.getElementById('labRequestShowError');
    const sampleSection = document.getElementById('sampleCollectionSection');
    const sampleForm = document.getElementById('sampleCollectionForm');
    const processingSection = document.getElementById('processingSection');
    const startProcessingButton = document.getElementById('startProcessingButton');
    const resultSummary = document.getElementById('labResultSummary');
    const resultButton = document.getElementById('showLabResultFormButton');
    const resultForm = document.getElementById('labResultForm');
    const printButton = document.getElementById('printLabReportButton');
    let labRequest;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const renderResult = () => {
        const result = labRequest.result;
        if (!result) {
            resultSummary.textContent = 'No result has been entered.';
            return;
        }

        resultSummary.innerHTML = `
            <div class="grid gap-5 rounded-xl bg-slate-50 p-5 md:grid-cols-2">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Result</p><p class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">${laboratoryEscapeHtml(result.result_value)}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Interpretation</p><p class="mt-1 text-sm font-semibold text-slate-900">${laboratoryEscapeHtml(statusLabel(result.interpretation))}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Unit / Reference Range</p><p class="mt-1 text-sm text-slate-700">${laboratoryEscapeHtml(result.unit || '—')} / ${laboratoryEscapeHtml(result.reference_range || '—')}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Entered</p><p class="mt-1 text-sm text-slate-700">${laboratoryEscapeHtml(result.entered_by || '—')} · ${laboratoryEscapeHtml(laboratoryFormatDateTime(result.resulted_at))}</p></div>
                ${result.remarks ? `<div class="md:col-span-2"><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Remarks</p><p class="mt-1 whitespace-pre-wrap text-sm text-slate-700">${laboratoryEscapeHtml(result.remarks)}</p></div>` : ''}
            </div>`;
    };

    const renderRequest = () => {
        setLaboratoryText('labRequestCode', labRequest.request_code);
        setLaboratoryText('labRequestTestName', labRequest.lab_test?.name);
        setLaboratoryText('labRequestStatus', statusLabel(labRequest.status));
        setLaboratoryText('labRequestPatient', `${labRequest.patient?.patient_code || ''} ${labRequest.patient?.full_name || ''}`.trim());
        setLaboratoryText('labRequestDoctor', labRequest.doctor?.name);
        setLaboratoryText('labRequestPriority', labRequest.priority);
        setLaboratoryText('labRequestRequestedAt', laboratoryFormatDateTime(labRequest.requested_at));
        setLaboratoryText('labRequestSpecimen', labRequest.lab_test?.specimen_type);
        setLaboratoryText('labRequestMedicalRecord', labRequest.medical_record?.record_code || 'Not linked');
        setLaboratoryText('labRequestSampleStatus', labRequest.specimen_condition ? statusLabel(labRequest.specimen_condition) : 'Not collected');
        setLaboratoryText('labRequestCollectedAt', laboratoryFormatDateTime(labRequest.sample_collected_at));
        setLaboratoryText('labRequestClinicalNotes', labRequest.clinical_notes);

        const canOperate = canOperateLaboratory(user);
        sampleSection.classList.toggle('hidden', !canOperate || labRequest.status !== 'REQUESTED');
        processingSection.classList.toggle('hidden', !canOperate || labRequest.status !== 'SAMPLE_COLLECTED');
        resultButton.classList.toggle('hidden', !canOperate || !['SAMPLE_COLLECTED', 'PROCESSING', 'COMPLETED'].includes(labRequest.status));
        printButton.classList.toggle('hidden', !(labRequest.status === 'COMPLETED' && labRequest.result));
        renderResult();
    };

    const loadRequest = async () => {
        try {
            const response = await apiRequest(`/lab-requests/${requestId}`);
            labRequest = response.data;
            renderRequest();
            errorBox.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };

    const showResultForm = () => {
        const result = labRequest.result;
        resultForm.elements.result_value.value = result?.result_value ?? '';
        resultForm.elements.unit.value = result?.unit ?? labRequest.lab_test?.unit ?? '';
        resultForm.elements.reference_range.value = result?.reference_range ?? labRequest.lab_test?.reference_range ?? '';
        resultForm.elements.interpretation.value = result?.interpretation ?? 'NORMAL';
        resultForm.elements.remarks.value = result?.remarks ?? '';
        document.getElementById('labResultedAt').value = laboratoryDateTimeInputValue(result?.resulted_at);
        resultForm.classList.remove('hidden');
        resultButton.classList.add('hidden');
    };

    const printReport = () => {
        const result = labRequest.result;
        if (!result) return;
        const reportWindow = window.open('', '_blank', 'width=900,height=700');
        if (!reportWindow) {
            showError('Allow pop-ups to print the laboratory report.');
            return;
        }
        reportWindow.document.write(`<!doctype html><html><head><title>${laboratoryEscapeHtml(labRequest.request_code)} Laboratory Report</title><style>body{font-family:Arial,sans-serif;color:#0f172a;margin:48px}header{border-bottom:2px solid #0891b2;padding-bottom:20px;margin-bottom:30px}h1{margin:0;font-size:26px}h2{font-size:17px;margin-top:30px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.label{font-size:11px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:.06em}.value{margin-top:5px;white-space:pre-wrap}section{margin-top:28px;padding:20px;border:1px solid #cbd5e1;border-radius:10px}@media print{body{margin:20px}}</style></head><body><header><h1>Medora Hospital Management</h1><p>Laboratory Report · ${laboratoryEscapeHtml(labRequest.request_code)}</p></header><div class="grid"><div><div class="label">Patient</div><div class="value">${laboratoryEscapeHtml(labRequest.patient?.full_name)} (${laboratoryEscapeHtml(labRequest.patient?.patient_code)})</div></div><div><div class="label">Requested By</div><div class="value">${laboratoryEscapeHtml(labRequest.doctor?.name)}</div></div><div><div class="label">Test</div><div class="value">${laboratoryEscapeHtml(labRequest.lab_test?.name)}</div></div><div><div class="label">Specimen</div><div class="value">${laboratoryEscapeHtml(labRequest.lab_test?.specimen_type)}</div></div></div><section><div class="label">Result</div><div class="value">${laboratoryEscapeHtml(result.result_value)}</div><div class="grid" style="margin-top:20px"><div><div class="label">Unit</div><div class="value">${laboratoryEscapeHtml(result.unit || '—')}</div></div><div><div class="label">Reference Range</div><div class="value">${laboratoryEscapeHtml(result.reference_range || '—')}</div></div><div><div class="label">Interpretation</div><div class="value">${laboratoryEscapeHtml(statusLabel(result.interpretation))}</div></div><div><div class="label">Resulted At</div><div class="value">${laboratoryEscapeHtml(laboratoryFormatDateTime(result.resulted_at))}</div></div></div>${result.remarks ? `<div style="margin-top:20px"><div class="label">Remarks</div><div class="value">${laboratoryEscapeHtml(result.remarks)}</div></div>` : ''}</section><p style="margin-top:36px;font-size:12px;color:#64748b">Generated by Medora HMS on ${laboratoryEscapeHtml(new Date().toLocaleString())}</p></body></html>`);
        reportWindow.document.close();
        reportWindow.focus();
        reportWindow.print();
    };

    sampleForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await apiRequest(`/lab-requests/${requestId}/collect-sample`, {
                method: 'PATCH',
                body: JSON.stringify(Object.fromEntries(new FormData(sampleForm).entries())),
            });
            await loadRequest();
        } catch (error) {
            showError(error.message);
        }
    });
    startProcessingButton.addEventListener('click', async () => {
        try {
            await apiRequest(`/lab-requests/${requestId}/start-processing`, { method: 'PATCH' });
            await loadRequest();
        } catch (error) {
            showError(error.message);
        }
    });
    resultButton.addEventListener('click', showResultForm);
    document.getElementById('cancelLabResultButton').addEventListener('click', () => {
        resultForm.classList.add('hidden');
        if (canOperateLaboratory(user)) resultButton.classList.remove('hidden');
    });
    resultForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const payload = Object.fromEntries(new FormData(resultForm).entries());
            Object.keys(payload).forEach((key) => {
                if (payload[key] === '') payload[key] = null;
            });
            await apiRequest(`/lab-requests/${requestId}/result`, { method: 'PUT', body: JSON.stringify(payload) });
            resultForm.classList.add('hidden');
            await loadRequest();
        } catch (error) {
            showError(error.message);
        }
    });
    printButton.addEventListener('click', printReport);

    await loadRequest();
};

const initialiseLabTestsPage = async (apiRequest, user) => {
    const root = document.getElementById('labTestsPage');
    if (!root) return;

    const errorBox = document.getElementById('labTestsError');
    const formSection = document.getElementById('labTestFormSection');
    const form = document.getElementById('labTestForm');
    const formHeading = document.getElementById('labTestFormHeading');
    const saveButton = document.getElementById('labTestSaveButton');
    const cancelEdit = document.getElementById('cancelLabTestEditButton');
    const filters = document.getElementById('labTestsFilters');
    const search = document.getElementById('labTestsSearch');
    const status = document.getElementById('labTestsStatusFilter');
    const tableBody = document.getElementById('labTestsTableBody');
    let editingTestId = null;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };
    const resetForm = () => {
        editingTestId = null;
        form.reset();
        form.elements.price.value = '0';
        form.elements.is_active.value = '1';
        formHeading.textContent = 'Add Laboratory Test';
        saveButton.textContent = 'Save Test';
        cancelEdit.classList.add('hidden');
    };
    if (!isAdministrator(user)) formSection.classList.add('hidden');

    const loadTests = async () => {
        const query = new URLSearchParams({ per_page: '50' });
        if (search.value.trim()) query.set('search', search.value.trim());
        if (status.value) query.set('status', status.value);
        try {
            const response = await apiRequest(`/lab-tests?${query}`);
            tableBody.replaceChildren();
            const tests = response.data ?? [];
            if (tests.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No laboratory tests found.</td></tr>';
                return;
            }
            tests.forEach((test) => {
                const row = document.createElement('tr');
                row.className = 'text-sm text-slate-700';
                row.innerHTML = `<td class="px-5 py-4"><p class="font-semibold text-slate-900">${laboratoryEscapeHtml(test.name)}</p><p class="mt-1 text-xs text-slate-500">${laboratoryEscapeHtml(test.test_code)} · ${laboratoryEscapeHtml(test.category || 'Uncategorized')}</p></td><td class="px-5 py-4">${laboratoryEscapeHtml(test.specimen_type)}</td><td class="px-5 py-4">${laboratoryEscapeHtml(test.reference_range || '—')}</td><td class="px-5 py-4">${laboratoryEscapeHtml(test.turnaround_hours ? `${test.turnaround_hours} hours` : '—')}</td><td class="px-5 py-4">${test.is_active ? 'Active' : 'Inactive'}</td><td class="px-5 py-4"><div class="flex gap-3"></div></td>`;
                if (isAdministrator(user)) {
                    const actions = row.querySelector('div');
                    const edit = document.createElement('button');
                    edit.type = 'button'; edit.className = 'font-semibold text-cyan-700'; edit.textContent = 'Edit';
                    edit.addEventListener('click', () => {
                        editingTestId = test.id;
                        form.elements.name.value = test.name;
                        form.elements.category.value = test.category ?? '';
                        form.elements.specimen_type.value = test.specimen_type;
                        form.elements.unit.value = test.unit ?? '';
                        form.elements.reference_range.value = test.reference_range ?? '';
                        form.elements.turnaround_hours.value = test.turnaround_hours ?? '';
                        form.elements.price.value = test.price;
                        form.elements.notes.value = test.notes ?? '';
                        form.elements.is_active.value = test.is_active ? '1' : '0';
                        formHeading.textContent = `Edit ${test.name}`;
                        saveButton.textContent = 'Update Test';
                        cancelEdit.classList.remove('hidden');
                        formSection.scrollIntoView({ behavior: 'smooth' });
                    });
                    const deactivate = document.createElement('button');
                    deactivate.type = 'button'; deactivate.className = 'font-semibold text-red-600'; deactivate.textContent = 'Deactivate';
                    deactivate.disabled = !test.is_active;
                    deactivate.addEventListener('click', async () => {
                        if (!window.confirm(`Deactivate ${test.name}? Existing results will be preserved.`)) return;
                        try { await apiRequest(`/lab-tests/${test.id}`, { method: 'DELETE' }); await loadTests(); } catch (error) { showError(error.message); }
                    });
                    actions.append(edit, deactivate);
                }
                tableBody.appendChild(row);
            });
            errorBox.classList.add('hidden');
        } catch (error) {
            showError(error.message);
        }
    };

    filters.addEventListener('submit', async (event) => { event.preventDefault(); await loadTests(); });
    cancelEdit.addEventListener('click', resetForm);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.price = Number(payload.price);
        payload.turnaround_hours = payload.turnaround_hours ? Number(payload.turnaround_hours) : null;
        payload.is_active = payload.is_active === '1';
        ['category', 'unit', 'reference_range', 'notes'].forEach((key) => { if (payload[key] === '') payload[key] = null; });
        try {
            await apiRequest(editingTestId ? `/lab-tests/${editingTestId}` : '/lab-tests', { method: editingTestId ? 'PUT' : 'POST', body: JSON.stringify(payload) });
            resetForm();
            await loadTests();
        } catch (error) {
            showError(error.message);
        }
    });

    await loadTests();
};

export const initialiseLaboratoryPages = async (apiRequest, user) => {
    await initialiseLaboratoryIndex(apiRequest, user);
    await initialiseLabRequestForm(apiRequest, user);
    await initialiseLabRequestShow(apiRequest, user);
    await initialiseLabTestsPage(apiRequest, user);
};
