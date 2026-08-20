const patientPortalSetText = (id, value) => {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value || '—';
    }
};

const patientPortalDate = (value) => value
    ? new Date(`${value}T00:00:00`).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
    : '—';

const patientPortalMessage = (type, message = '') => {
    const element = document.getElementById(`patientPortal${type}`);

    if (!element) {
        return;
    }

    element.textContent = message;
    element.classList.toggle('hidden', !message);
};

const patientPortalDisplay = (patient) => {
    patientPortalSetText(
        'patientPortalGreeting',
        `Welcome, ${patient.first_name || 'there'}`
    );
    patientPortalSetText('patientPortalCode', patient.patient_code);
    patientPortalSetText('patientPortalName', patient.full_name);
    patientPortalSetText('patientPortalDob', patientPortalDate(patient.date_of_birth));
    patientPortalSetText('patientPortalBloodGroup', patient.blood_group);
    patientPortalSetText('patientPortalUsername', patient.account?.username);
    patientPortalSetText('patientPortalGender', patient.gender?.replaceAll('_', ' '));
    patientPortalSetText('patientPortalPhone', patient.phone);
    patientPortalSetText('patientPortalEmail', patient.email);

    const emergency = [
        patient.emergency_contact_name,
        patient.emergency_contact_relation,
        patient.emergency_contact_phone,
    ].filter(Boolean).join(' · ');

    patientPortalSetText('patientPortalEmergency', emergency);
};

const patientPortalFillForm = (patient) => {
    const form = document.getElementById('patientPortalProfileForm');

    if (!form) {
        return;
    }

    [
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
    ].forEach((field) => {
        form.elements[field].value = patient[field] ?? '';
    });
};

export const initialisePatientPortal = async (apiRequest, user) => {
    const page = document.getElementById('patientPortalPage');

    if (!page) {
        return;
    }

    if (user?.role?.slug !== 'PATIENT') {
        patientPortalMessage('Error', 'This portal is available to patient accounts only.');
        return;
    }

    const form = document.getElementById('patientPortalProfileForm');
    const saveButton = document.getElementById('patientPortalSaveButton');

    const loadProfile = async () => {
        patientPortalMessage('Error');

        try {
            const response = await apiRequest('/patient-portal/profile');
            const patient = response.data.patient;

            patientPortalDisplay(patient);
            patientPortalFillForm(patient);
        } catch (exception) {
            patientPortalMessage(
                'Error',
                exception.message ?? 'Your patient profile could not be loaded.'
            );
        }
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        patientPortalMessage('Error');
        patientPortalMessage('Success');

        const payload = Object.fromEntries(new FormData(form).entries());

        Object.keys(payload).forEach((key) => {
            if (typeof payload[key] === 'string') {
                payload[key] = payload[key].trim();
            }

            if (payload[key] === '') {
                payload[key] = null;
            }
        });

        if (!payload.password) {
            delete payload.current_password;
            delete payload.password;
            delete payload.password_confirmation;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await apiRequest('/patient-portal/profile', {
                method: 'PATCH',
                body: JSON.stringify(payload),
            });
            const patient = response.data.patient;

            patientPortalDisplay(patient);
            patientPortalFillForm(patient);
            form.elements.current_password.value = '';
            form.elements.password.value = '';
            form.elements.password_confirmation.value = '';

            const currentUserName = document.getElementById('currentUserName');

            if (currentUserName) {
                currentUserName.textContent = patient.full_name;
            }

            patientPortalMessage(
                'Success',
                response.message ?? 'Your profile details have been updated.'
            );
        } catch (exception) {
            patientPortalMessage(
                'Error',
                exception.message ?? 'Your profile details could not be saved.'
            );
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save profile details';
        }
    });

    await loadProfile();
};
