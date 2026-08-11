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