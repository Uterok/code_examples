class AxiosHTTPClientHandler {
    constructor(http_client, urls, event_bus, reauthCallback) {
        this.http_client = http_client;
        this.urls = urls;
        this.reauthCallback = reauthCallback;
        this.event_bus = event_bus;
        this.refresh_token_requests_info = {
            url: null,
            sent: false,
            sent_repeat_request: false
        };
        this.requests_sent = [];

        this.initAxiosInterceptors();
        this.event_bus.on('access-token-changed', (access_token) => this.setAuthorizationBearerHeader(access_token));
    }

    initAxiosInterceptors() {
        this.http_client.interceptors.request.use((config) => {
            return config;

        }, function (error) {

            return Promise.reject(error);
        });

        this.http_client.interceptors.response.use(
            response => {
                if (this.refresh_token_requests_info.url == response.config.url) {
                    this.refresh_token_requests_info = {
                        url: null,
                        sent: false
                    };
                }
                this.event_bus.emit('sent-request-ok', response.config.url);

            return response;
        },
        error => {
            if(error.response.status == 401) {

                if ((this.refresh_token_requests_info.url == error.response.config.url) ||
                    (error.response.config.url == this.urls.refresh_url)
                    ) {
                    this.refresh_token_requests_info = {
                        url: null,
                        sent: false
                    };

                    return Promise.reject(error);
                } else if (this.refresh_token_requests_info.sent == true) {
                    return new Promise((resolve, reject) => {
                        let unsubscrube_events = null;
                        let req_with_new_token_success = () => {
                            unsubscrube_events();
                            return this.http_client(this.getConfigToRepeatRequest(error.response.config))
                            .then(response => {
                                return Promise.resolve(response);
                            })
                            .catch(error => {
                                this.event_bus.emit('sent-request-error', error.response.config.url);
                                return Promise.reject(error);
                            });   
                        }
                        let req_with_new_token_error = () => {
                            reject('wrong refreshed token');
                            unsubscrube_events();
                        }
                        unsubscrube_events = () => {
                            this.event_bus.off('request-with-new-token-success', req_with_new_token_success);
                            this.event_bus.off('request-with-new-token-failed', req_with_new_token_error);
                        }

                        this.event_bus.on('request-with-new-token-success', req_with_new_token_success);
                        this.event_bus.on('request-with-new-token-failed', req_with_new_token_error);
                    });
                } else {
                    this.refresh_token_requests_info.sent = true;
                }

                let repeat_request_config = this.getConfigToRepeatRequest(error.response.config);
                this.refresh_token_requests_info.url = repeat_request_config.url;
                return this.reauthCallback(
                    success => {

                        return this.http_client(repeat_request_config)
                            .then(response => {
                                this.event_bus.emit('request-with-new-token-success');
                                return Promise.resolve(response);
                            })
                            .catch(error => {
                                this.event_bus.emit('request-with-new-token-failed');
                                this.event_bus.emit('sent-request-error', error.response.config.url);
                                return Promise.reject(error);
                            });
                    },
                    error => {
                        return Promise.reject(error);
                    });
            }

            return Promise.reject(error);
        });
    }

    getConfigToRepeatRequest(returned_config) {
        let config = {
            method: returned_config.method,
            url: returned_config.url.replace(returned_config.baseURL, ''),
            data: returned_config.data ? JSON.parse(returned_config.data) : null,
            params: returned_config.params ? returned_config.params : null,
        };

        return config;
    }

    getHTTPHeaders() {
        return this.http_client.defaults.headers.common;
    }

    setAuthorizationBearerHeader(access_token) {
        this.http_client.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
    }

    clearAxiosConfigOnLogout() {
        if(this.http_client.defaults.headers.common['Authorization']) {
            delete this.http_client.defaults.headers.common['Authorization'];
        } 
    }

}

class AuthController {
    constructor(http_client, urls, event_bus, userAuthenticated) {
        this.register_url = urls.register_url;
        this.login_url = urls.login_url;
        this.check_is_user_auth_url = urls.check_is_user_auth_url;
        this.logout_url = urls.logout_url;

        this.http_client = http_client;

        this.userAuthenticated = userAuthenticated;
        this.event_bus = event_bus;
    }

    register(register_data) {
        return new Promise((resolve, reject) => {
            this.http_client.post(this.register_url, register_data)
                .then(response => {
                    resolve(response.data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    login(login_data) {
        return new Promise((resolve, reject) => {
            this.http_client.post(this.login_url, login_data)
                .then(response => {
                    let auth_data = response.data;
                    this.userAuthenticated(auth_data).then(success => resolve()).catch(error => reject(error));
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    checkAuthUser() {
        return new Promise((resolve, reject) => {
            this.http_client.get(this.check_is_user_auth_url)
                .then(response => {
                    let auth_data = response.data;
                    resolve(auth_data);
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    //logout methods
    logout() {
        return this.http_client.post(this.logout_url)
                    .then(response => {
                        Promise.resolve(response);
                    })
                    .catch(error => {
                        Promise.reject(error);
                    });
    }

}

class AuthUser {
    constructor(http_client, urls) {
        this.http_client = http_client;

        this.check_is_user_auth_url = urls.check_is_user_auth_url;
        // this.user_role_url = urls.user_role_url;

        this.profile_info = null;
        this.role = null;
        this.user_auth_flag = false;
    }

    setAuthUserFlag(flag) {
        if(this.user_auth_flag != flag) this.user_auth_flag = flag;
    }

    getAuthUserFlag() {
        // console.log("AUTH FLAG GET ", this.user_auth_flag);
        return this.user_auth_flag;
    }

    accessSumOfNumb(str) {
        let sum = 0;
        for(let i = 0; i < str.length; i++) {
            if(!isNaN(str[i])) {
                sum += +str[i];
            }               
        }
        return sum * 777;
    }

    hashRoleNumb(role_numb) {
        return role_numb + this.accessSumOfNumb(this.access_token.access_token);
    }

    getRoleNumber() {
        // console.log(this);
        return this.profile_info.role.id - this.accessSumOfNumb(this.access_token.access_token);
    }
}

class TokenAuthentication {
    constructor(http_client, urls, event_bus) {
        this.http_client = http_client;
        this.urls = urls;
        this.event_bus = event_bus;
        this.refresh_url = urls.refresh_url;

        this.token_refreshing_flag = false;

        this.access_token = null;

    }

    handleReceivedToken(token_obj) {
        if(this.access_token && (this.access_token.access_token == token_obj.access_token)) return token_obj;

        this.access_token = {access_token: token_obj.access_token};

        if(token_obj && !token_obj.time_calculated && !token_obj.time_until_expiration && token_obj.expires_in) {
            this.access_token.time_until_expiration = +new Date + token_obj.expires_in * 1000;
            localStorage.setItem('accessToken', JSON.stringify(this.access_token));
        }
        else {
            this.access_token.time_until_expiration = token_obj.time_until_expiration;
        }

        return this.access_token;
    }

    getAccessToken() {
        return JSON.parse(localStorage.getItem('accessToken'));
    }

    checkToken(is_app_created = false) {
        return new Promise((resolve, reject) => {
            if(!this.access_token) {
                this.access_token = this.getAccessToken();

                if(!this.access_token || this.isNeedUpdateToken()) {

                    if(is_app_created) {
                        this.attemptReauthorize(
                            success => {
                                resolve(this.access_token);
                                //
                            },
                            error => {
                                reject(error);
                            },
                            false);
                    }
                    else {
                        reject('token is ABSENT');
                    }
                }
                else {
                    resolve(this.access_token);
                }
            }
            else {
                resolve(this.access_token);
            }
        });
    }

    isNeedUpdateToken() {
        if(!this.access_token) return true;

        let current_time = +new Date;
        let until_expiration = this.access_token.time_until_expiration - current_time - 10000;

        return until_expiration <= 0 ? true : false;
    }

    emitAuthErrorIfNotYet(notify_if_failed = true) {
        if(this.token_refreshing_flag == false) {
            this.onAuthorizedErrorHandler(notify_if_failed);
        }
    }

    attemptReauthorize(callback_succsess, callback_error, notify_if_failed = true) {
        this.emitAuthErrorIfNotYet(notify_if_failed);

        return this.resultOfTokenRefreshing().then(callback_succsess, callback_error);
    }

    resultOfTokenRefreshing() {
        return new Promise((resolve, reject) => {
                this.event_bus.on('refresh-token-success', () => resolve());
                this.event_bus.on('refresh-token-error', () => reject('token refreshing error'));
        });
    }

    attemptRefreshToken() {
        return new Promise((resolve, reject) => {
            this.http_client.post(this.refresh_url)
                .then(response => {
                    let access_token = response.data;

                    this.handleReceivedToken(access_token);
                    this.token_refreshing_flag = false;
                    this.event_bus.emit('access-token-changed', access_token.access_token);

                    resolve();
                })
                .catch(error => {
                    this.token_refreshing_flag = false;
                    reject();
                });
        });
    }

    //error handlers
    onAuthorizedErrorHandler(notify_if_failed = true) {
        this.token_refreshing_flag = true;

        this.attemptRefreshToken().then(
            success => {
                this.event_bus.emit('refresh-token-success');
            },
            error => {
                this.event_bus.emit('refresh-token-error');
                if(notify_if_failed) {
                    this.event_bus.emit('unauthenticated');
                }
            });
    }

    clearTokenData() {
        localStorage.removeItem('accessToken');
        this.access_token = null;
    }
}

class EventBus {
    constructor(bus = null) {
        this.bus = bus;
    }

    setBus(bus) {
        this.bus = bus;
    }

    getBus() {
        return this.bus;
    }

    //events middlewares
    emit(event_name, ...data) {
        if(this.bus) {
            this.bus.$emit(event_name, ...data);
        }
    }

    on(event_name, callback) {
        if(this.bus) {
            this.bus.$on(event_name, callback);
        }
    }

    off(event_name, callback) {
        if(this.bus) {
            this.bus.$off(event_name, callback);
        }
    }
}

export class AppAuthentication {
    constructor(http_client, urls, logoutCallback = null, event_bus = null) {
        this.http_client = http_client;
        this.urls = urls;
        this.logoutCallback = logoutCallback;
        this.subscribed_main_events = false;

        this.app = null;

        this.events = {};

        this.event_bus = new EventBus(event_bus);
        if (event_bus) {
            this.subscribeEvents();
        }

        this.token_auth = new TokenAuthentication(http_client,
                                                  urls,
                                                  this.event_bus,
                                                  );
        this.http_client_handler = new AxiosHTTPClientHandler(http_client,
                                                  urls,
                                                  this.event_bus,
                                                  this.token_auth.attemptReauthorize.bind(this.token_auth)
                                                  );
        this.auth_user = new AuthUser(http_client, urls);
        this.auth_controller = new AuthController(http_client,
                                                  urls,
                                                  this.event_bus,
                                                  (...data) => this.userAuthenticated(...data)
                                                  );
        this.attemptFindToken();
        this.event_bus.on('call-logout-callback', () => this.callLogoutCallback());
    }

    attemptFindToken() {
        let access_token = this.token_auth.getAccessToken();
            if (access_token) {
                this.event_bus.emit('access-token-changed', (() => {
                return access_token ? access_token.access_token : null;
            })());
            this.userAuthenticated(access_token);
        }
    }

    setEventBus(bus) {
        if (bus) {
            this.event_bus.setBus(bus);
            this.subscribeEvents();
        }
    }

    setApp(app) {
        if(!this.app) this.app = app;
        this.subscribeEvents();
    }

    setLogoutCallback(callback) {
        this.logoutCallback = callback;
    }

    callLogoutCallback() {
        this.logoutCallback();
    }

    subscribeEvents() {
        if (!this.subscribed_main_events) {
            this.event_bus.on('user-logout-submit', (...data) => this.logout(...data));
            this.event_bus.on('unauthenticated', () => this.unauthenticated());
            this.subscribed_main_events = true;
        }
    }

    async checkUserAuth() {
        let access_token = await this.token_auth.checkToken(this.app ? true : false);

        await this.userAuthenticated(access_token).catch(error => Promise.reject(error));

        return true;
    }

    async userAuthenticated(access_token) {

        this.token_auth.handleReceivedToken(access_token);
        this.event_bus.emit('access-token-changed', access_token.access_token);
        this.auth_user.setAuthUserFlag(true);
    }

    unauthenticated() {
        this.logout();
        this.event_bus.emit('call-logout-callback');

    }

    isUserAuthenticated() {
        return this.auth_user.getAuthUserFlag();
    }

    isUserInRole(role_name) {
        let role = this.getAuthUserRole();

        if(role_name == 'Admin' || role_name == 'admin') {
            if(role.id == 1) return true;
        }
        else if(role_name == 'Client' || role_name == 'client') {
            if(role.id == 2) return true;
        }
        else if(role_name == 'Manager' || role_name == 'manager') {
            if(role.id == 3) return true;
        }

        return false;
    }

    getHTTPHeaders() {
        return this.http_client_handler.getHTTPHeaders();
    }

    //auth actions
    async login(login_data) {
        return await this.auth_controller.login(login_data);
    }

    async register(register_data) {
        return await this.auth_controller.register(register_data);
    }

    async checkAuthUser() {
        return await this.auth_controller.checkAuthUser();
    }

    async logout() {
        if(this.isUserAuthenticated()) {
            return this.auth_controller.logout().then(
                        success => {
                            this.token_auth.clearTokenData();
                            this.http_client_handler.clearAxiosConfigOnLogout();
                            this.auth_user.setAuthUserFlag(false);
                            return Promise.resolve({status: 1});
                        },
                        error => {
                            return Promise.reject(error);
                        });
        } else {
            this.token_auth.clearTokenData();
            this.http_client_handler.clearAxiosConfigOnLogout();
            return Promise.resolve({status: 1});
        }
    }

    //unauth errors handlers
    vueTableLoadErrorHandler(context, error) {
        if(error.response.status == 401) {
            this.token_auth.attemptReauthorize(
                success => {
                    context.$emit('need-update-page');
                },
                error => {

                });
        }
    }

    vueDropzoneCheckTokenBeforeLoad(dropzone_instance) {

        if(this.token_auth.isNeedUpdateToken()) {
            this.token_auth.attemptReauthorize(
                success => {
                    dropzone_instance.processQueue();
                },
                error => {
                    dropzone_instance.removeAllFiles();
                });
        }
        else {
            dropzone_instance.processQueue();
        }
    }
}

