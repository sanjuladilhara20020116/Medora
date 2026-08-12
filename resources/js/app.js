const TOKEN_KEY = 'medora_access_token';

const getToken = () => sessionStorage.getItem(TOKEN_KEY);

const saveToken = (token) => {
    sessionStorage.setItem(TOKEN_KEY, token);
};

const clearToken = () => {
    sessionStorage.removeItem(TOKEN_KEY);
};

const redirectToLogin = () => {
    window.location.replace('/login');
};

const redirectToDashboard = () => {
    window.location.replace('/dashboard');
};

const getErrorMessage = (payload) => {
    if (payload?.errors) {
        const firstErrorGroup = Object.values(payload.errors)[0];

        if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
            return firstErrorGroup[0];
        }
    }

    return payload?.message ?? 'Something went wrong. Please try again.';
};

const apiRequest = async (endpoint, options = {}) => {
    const headers = {
        Accept: 'application/json',
        ...(options.headers ?? {}),
    };

    const token = getToken();

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    if (options.body && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(`/api${endpoint}`, {
        ...options,
        headers,
    });

    let payload = {};

    try {
        payload = await response.json();
    } catch {
        payload = {};
    }

    if (!response.ok) {
        const error = new Error(getErrorMessage(payload));
        error.status = response.status;
        error.payload = payload;

        throw error;
    }

    return payload;
};


/*
|--------------------------------------------------------------------------
| Login Page
|--------------------------------------------------------------------------
*/

const initialiseLoginPage = async () => {
    const form = document.getElementById('loginForm');

    if (!form) {
        return;
    }

    const existingToken = getToken();

    if (existingToken) {
        try {
            await apiRequest('/auth/me');
            redirectToDashboard();
            return;
        } catch {
            clearToken();
        }
    }

    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const errorBox = document.getElementById('loginError');
    const loginButton = document.getElementById('loginButton');
    const loginButtonText = document.getElementById('loginButtonText');

    togglePassword?.addEventListener('click', () => {
        const passwordVisible = passwordInput.type === 'text';

        passwordInput.type = passwordVisible ? 'password' : 'text';
        togglePassword.textContent = passwordVisible ? 'Show' : 'Hide';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        errorBox.classList.add('hidden');
        errorBox.textContent = '';

        loginButton.disabled = true;
        loginButtonText.textContent = 'Signing in...';

        const login = document.getElementById('login').value.trim();
        const password = passwordInput.value;

        try {
            const response = await apiRequest('/auth/login', {
                method: 'POST',

                body: JSON.stringify({
                    login,
                    password,
                }),
            });

            const token = response?.data?.authorization?.token;

            if (!token) {
                throw new Error('Authentication token was not returned.');
            }

            saveToken(token);
            redirectToDashboard();

        } catch (error) {
            clearToken();

            errorBox.textContent = error.message;
            errorBox.classList.remove('hidden');

        } finally {
            loginButton.disabled = false;
            loginButtonText.textContent = 'Sign in securely';
        }
    });
};

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

const setDashboardText = (id, value) => {
    const element = document.getElementById(id);

    if (element) {
        element.textContent = value;
    }
};


const renderRoleDistribution = (roles = []) => {
    const container =
        document.getElementById('roleDistribution');

    if (!container) {
        return;
    }

    container.replaceChildren();

    if (roles.length === 0) {
        container.textContent =
            'No role information is available.';

        return;
    }

    const maximum =
        Math.max(
            ...roles.map((role) => Number(role.total)),
            1
        );

    roles.forEach((role) => {

        const wrapper =
            document.createElement('div');


        const header =
            document.createElement('div');

        header.className =
            'mb-2 flex items-center justify-between';


        const name =
            document.createElement('span');

        name.className =
            'text-sm font-medium text-slate-700';

        name.textContent =
            role.name;


        const count =
            document.createElement('span');

        count.className =
            'text-sm font-bold text-slate-950';

        count.textContent =
            role.total;


        header.append(name, count);


        const track =
            document.createElement('div');

        track.className =
            'h-2 overflow-hidden rounded-full bg-slate-100';


        const bar =
            document.createElement('div');

        bar.className =
            'h-full rounded-full bg-cyan-500';

        bar.style.width =
            `${(Number(role.total) / maximum) * 100}%`;


        track.appendChild(bar);

        wrapper.append(header, track);

        container.appendChild(wrapper);

    });
};


const renderRecentLogins = (users = []) => {
    const container =
        document.getElementById('recentLogins');

    if (!container) {
        return;
    }

    container.replaceChildren();


    if (users.length === 0) {

        const empty =
            document.createElement('p');

        empty.className =
            'py-4 text-sm text-slate-400';

        empty.textContent =
            'No login activity available.';

        container.appendChild(empty);

        return;
    }


    users.forEach((user) => {

        const row =
            document.createElement('div');

        row.className =
            'flex items-center justify-between gap-4 py-4';


        const details =
            document.createElement('div');


        const name =
            document.createElement('p');

        name.className =
            'text-sm font-semibold text-slate-900';

        name.textContent =
            user.name;


        const role =
            document.createElement('p');

        role.className =
            'mt-1 text-xs text-slate-500';

        role.textContent =
            user.role?.name ?? 'No role';


        details.append(name, role);


        const time =
            document.createElement('p');

        time.className =
            'text-right text-xs text-slate-400';

        time.textContent =
            user.last_login_at
                ? new Date(
                    user.last_login_at
                ).toLocaleString()
                : 'Never';


        row.append(details, time);

        container.appendChild(row);

    });
};


const loadAdminDashboard = async () => {
    const root =
        document.getElementById('dashboardDataRoot');

    if (!root) {
        return;
    }


    const errorBox =
        document.getElementById('dashboardError');


    try {

        const response =
            await apiRequest('/dashboard/admin');

        const data =
            response.data;


        /*
         * Core statistics
         */

        setDashboardText(
            'statTotalUsers',
            data.core_statistics.total_users
        );

        setDashboardText(
            'statActiveUsers',
            data.core_statistics.active_users
        );

        setDashboardText(
            'statActiveRoles',
            data.core_statistics.active_roles
        );

        setDashboardText(
            'statDepartments',
            data.core_statistics.active_departments
        );


        /*
         * Module statistics
         */

        Object.entries(
            data.module_statistics
        ).forEach(([key, metric]) => {

            const valueElement =
                document.getElementById(
                    `module-${key}-value`
                );

            const statusElement =
                document.getElementById(
                    `module-${key}-status`
                );


            if (!valueElement || !statusElement) {
                return;
            }


            if (metric.available) {

                valueElement.textContent =
                    metric.value;

                statusElement.textContent =
                    'Live database data';

            } else {

                valueElement.textContent =
                    '—';

                statusElement.textContent =
                    'Module not initialized';

            }

        });


        /*
         * Other dashboard components
         */

        renderRoleDistribution(
            data.role_distribution
        );

        renderRecentLogins(
            data.recent_logins
        );


        setDashboardText(
            'dashboardUpdatedAt',
            new Date(
                data.generated_at
            ).toLocaleString()
        );


        errorBox?.classList.add('hidden');

    } catch (error) {

        if (errorBox) {

            errorBox.textContent =
                error.message ??
                'Dashboard information could not be loaded.';

            errorBox.classList.remove(
                'hidden'
            );

        }

    }
};


/*
|--------------------------------------------------------------------------
| Authenticated HMS Application
|--------------------------------------------------------------------------
*/

const initialiseApplication = async () => {
    const shell = document.getElementById('appShell');

    if (!shell) {
        return;
    }

    const token = getToken();

    if (!token) {
        redirectToLogin();
        return;
    }

    try {
        const response = await apiRequest('/auth/me');

        const user = response.data.user;

        const userName = document.getElementById('currentUserName');
        const userRole = document.getElementById('currentUserRole');
        const userAvatar = document.getElementById('userAvatar');

        if (userName) {
            userName.textContent = user.name;
        }

        if (userRole) {
            userRole.textContent = user.role?.name ?? 'No role';
        }

        if (userAvatar) {
            userAvatar.textContent =
                user.name?.charAt(0)?.toUpperCase() ?? 'M';
        }

        shell.classList.remove('hidden');
        if (user.role?.slug === 'ADMIN') {
    await loadAdminDashboard();
}

    } catch {
        clearToken();
        redirectToLogin();
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    const logoutButton = document.getElementById('logoutButton');

    logoutButton?.addEventListener('click', async () => {
        logoutButton.disabled = true;
        logoutButton.textContent = 'Logging out...';

        try {
            await apiRequest('/auth/logout', {
                method: 'POST',
            });
        } catch {
            // Clear browser session even if JWT is already invalid.
        } finally {
            clearToken();
            redirectToLogin();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const menuButton = document.getElementById('mobileMenuButton');

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
    };

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
    };

    menuButton?.addEventListener('click', openSidebar);
    backdrop?.addEventListener('click', closeSidebar);
};


document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;

    if (page === 'login') {
        initialiseLoginPage();
    }

    if (page === 'app') {
        initialiseApplication();
    }
});