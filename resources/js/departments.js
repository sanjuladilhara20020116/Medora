export const initialiseDepartmentsPage =
async (apiRequest, user) => {

    const root =
        document.getElementById(
            'departmentsPage'
        );

    if (!root) {
        return;
    }


    const form =
        document.getElementById(
            'departmentForm'
        );

    const table =
        document.getElementById(
            'departmentsTableBody'
        );

    const search =
        document.getElementById(
            'departmentSearch'
        );

    const errorBox =
        document.getElementById(
            'departmentError'
        );

    const idField =
        document.getElementById(
            'departmentId'
        );

    const title =
        document.getElementById(
            'departmentFormTitle'
        );

    const cancel =
        document.getElementById(
            'departmentCancelButton'
        );


    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };


    const clearForm = () => {
        form.reset();

        idField.value = '';

        title.textContent =
            'Add Department';

        cancel.classList.add('hidden');
    };


    const loadDepartments =
    async () => {

        try {

            errorBox.classList.add(
                'hidden'
            );


            const params =
                new URLSearchParams({
                    per_page: '100',
                });


            if (search.value.trim()) {
                params.set(
                    'search',
                    search.value.trim()
                );
            }


            const response =
                await apiRequest(
                    `/departments?${params}`
                );


            table.replaceChildren();


            response.data.forEach(
                (department) => {

                    const row =
                        document.createElement(
                            'tr'
                        );

                    row.className =
                        'text-sm';


                    row.innerHTML = `
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-950">
                                ${department.name}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                ${department.code}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            ${department.location ?? '—'}
                        </td>

                        <td class="px-5 py-4">
                            ${department.doctors_count ?? 0}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    data-edit
                                    class="font-semibold text-cyan-700"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    data-delete
                                    class="font-semibold text-red-600"
                                >
                                    Archive
                                </button>
                            </div>
                        </td>
                    `;


                    const editButton =
                        row.querySelector(
                            '[data-edit]'
                        );

                    const deleteButton =
                        row.querySelector(
                            '[data-delete]'
                        );


                    if (
                        user.role?.slug !==
                        'ADMIN'
                    ) {
                        editButton?.remove();
                        deleteButton?.remove();
                    }


                    editButton
                        ?.addEventListener(
                            'click',
                            () => {

                                idField.value =
                                    department.id;

                                form.elements.code.value =
                                    department.code;

                                form.elements.name.value =
                                    department.name;

                                form.elements.phone.value =
                                    department.phone ?? '';

                                form.elements.email.value =
                                    department.email ?? '';

                                form.elements.location.value =
                                    department.location ?? '';

                                form.elements.description.value =
                                    department.description ?? '';

                                title.textContent =
                                    'Edit Department';

                                cancel.classList.remove(
                                    'hidden'
                                );
                            }
                        );


                    deleteButton
                        ?.addEventListener(
                            'click',
                            async () => {

                                if (
                                    !window.confirm(
                                        'Archive this department?'
                                    )
                                ) {
                                    return;
                                }


                                try {

                                    await apiRequest(
                                        `/departments/${department.id}`,
                                        {
                                            method:
                                                'DELETE',
                                        }
                                    );

                                    await loadDepartments();

                                } catch (error) {

                                    showError(
                                        error.message
                                    );
                                }
                            }
                        );


                    table.appendChild(row);
                }
            );

        } catch (error) {

            showError(
                error.message
            );
        }
    };


    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            const departmentId =
                idField.value;


            const formData =
                new FormData(form);


            const payload =
                Object.fromEntries(
                    formData.entries()
                );


            Object.keys(payload)
                .forEach((key) => {

                    if (
                        payload[key] === ''
                    ) {
                        payload[key] = null;
                    }
                });


            try {

                await apiRequest(
                    departmentId
                        ? `/departments/${departmentId}`
                        : '/departments',
                    {
                        method:
                            departmentId
                                ? 'PUT'
                                : 'POST',

                        body:
                            JSON.stringify(
                                payload
                            ),
                    }
                );


                clearForm();

                await loadDepartments();

            } catch (error) {

                showError(
                    error.message
                );
            }
        }
    );


    cancel.addEventListener(
        'click',
        clearForm
    );


    search.addEventListener(
        'input',
        () => {

            clearTimeout(
                search._timer
            );

            search._timer =
                setTimeout(
                    loadDepartments,
                    300
                );
        }
    );


    if (
        user.role?.slug !==
        'ADMIN'
    ) {
        form
            .closest('section')
            ?.classList.add(
                'hidden'
            );
    }


    await loadDepartments();
};