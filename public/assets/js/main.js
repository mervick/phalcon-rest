;(function($, Backbone, toastr, win, undefined) {
    'use strict';

    // Init toastr options
    toastr.options = {
        'progressBar': false,
        'positionClass': 'toast-top-center',
        'preventDuplicates': true
    };

    // Init slide up/down alert notice
    $(document).on('click', '.alert-notice', function() {
        var self = $(this),
            group = self.closest('.form-group'),
            alert = group.find('.alert');

        if (alert.is(':hidden')) {
            alert.slideDown();
        } else {
            alert.slideUp();
        }
    });

    // Clear validations when value was changed
    $(document).on('change', '.has-errors input', function() {
        $(this).closest('.has-errors').removeClass('has-errors');
    });

    /**
     * Local storage
     * @type {{parse, get, set, remove}}
     */
    var Storage = (function () {
        var self = {
            enabled: function() {
                try { return ('localStorage' in win && win['localStorage']) }
                catch(err) { return false }
            } ()
        };

        if (self.enabled) {
            var storage = win['localStorage'];
            $.extend(self, {
                /**
                 * Parse data from value, supports arrays, objects, numbers and strings
                 * @param value
                 * @returns {Array|Object|Number|String|*}
                 */
                parse: function (value) {
                    if (typeof value != 'string') {
                        return undefined;
                    }
                    try {
                        return JSON.parse(value);
                    }
                    catch (e) {
                        return value || undefined;
                    }
                },

                /**
                 * Set value int storage
                 * @param {*} key
                 * @param {*} val
                 * @returns {*}
                 */
                'set': function (key, val) {
                    if (val === undefined) {
                        return self.remove(key);
                    }
                    storage.setItem(key, JSON.stringify(val));
                    return val;
                },

                /**
                 * Get value from storage
                 * @param {*} key
                 * @param {*} [defaultVal]
                 * @returns {*}
                 */
                'get': function (key, defaultVal) {
                    var val = self.parse(storage.getItem(key));
                    return (val === undefined ? defaultVal : val);
                },

                /**
                 * Remove data from storage by key
                 * @param {*} key
                 */
                remove: function (key) {
                    storage.removeItem(key);
                }
            });

            try {
                var testKey = '__STORAGE__';
                self.set(testKey, testKey);
                if (self.get(testKey) != testKey) {
                    self.enabled = false;
                } else {
                    self.remove(testKey);
                }
            } catch(e) {
                self.enabled = false;
            }
        }

        return self;
    }) ();

    /**
     * reCaptcha object
     * @type {{render, check}}
     */
    var reCaptcha = (function() {
        var sitekey = '6LetqEUUAAAAAKVr-36FxEcXy_Wng1ykig-kMyNo',
            container = 'g-recaptcha-container',
            field = 'g-recaptcha-response',
            reCaptchaID = null,
            loaded = false;

        window.reCaptchaCallback = function() {
            loaded = true;
            reCaptcha.render();
        };

        return {
            /**
             * Render reCaptcha
             */
            render: function() {
                var el;
                // Avoid error for empty container
                if (!loaded || !((el = $('#' + container))[0])) return;

                // Recreate container for create new reCaptcha
                $('<div/>').insertAfter(el.html('')).attr('id', container);
                el.remove();

                reCaptchaID = win['grecaptcha'].render(container, {
                    'sitekey': sitekey
                });
                return reCaptchaID;
            },

            /**
             * Check whether reCaptcha passed validation
             * @returns {boolean}
             */
            check: function() {
                if (!loaded || !win['grecaptcha'] || reCaptchaID === null) {
                    return false;
                }
                /** @typedef {{getResponse}} win['grecaptcha'] */
                var response = win['grecaptcha'].getResponse(reCaptchaID);

                if (response.length == 0) {
                    validation.show($('#content'), field, [
                        'ReCaptcha validation is required'
                    ]);
                    return false;
                }
                else {
                    validation.hide($('#content'), field);
                    return true;
                }
            }
        };
    }) ();

    /**
     * Validation helper
     * @type {{show, hide}}
     */
    var validation = {};

    /**
     * Show validation errors under the field
     * @param {jQuery} parent
     * @param {String} name
     * @param {Array} errors
     */
    validation.show = function(parent, name, errors) {
        if (errors && errors.length) {
            var container = parent.find('.errors').filter('[data-field=' + name + ']').html('');
            $.each(errors, function (i, error) {
                container.append(
                    '<p><small>' + error + '</small></p>'
                );
            });
            container.closest('.form-group').addClass('has-errors');
        }
    };

    /**
     * Hide validation errors under the field
     * @param {jQuery} parent
     * @param {String} [name]
     */
    validation.hide = function(parent, name) {
        if (name) {
            parent.find('.errors').filter('[data-field=' + name + ']').html('')
                .closest('.has-errors').removeClass('has-errors');
        } else {
            parent.find('.has-errors').removeClass('has-errors');
        }
    };

    /**
     * Checks response, shows messages and highlights errors
     * @type {Function}
     * @param {Object|XMLHttpRequest} data
     * @returns {Object|*}
     */
    var checkResponse = (function() {
        var handle = function(data) {
            var $form = $('#content').find('form');

            if ($form && $form[0]) {
                reCaptcha.render();
            }

            if (data.status) {
                // Clear old errors
                validation.hide($form);

                if (data.status == 'OK') {
                    if (data.message) {
                        toastr.success(data.message);
                    }
                    return data;
                }
                else if (data.message) {
                    toastr.error(data.message);

                    // Show validation errors for fields
                    if (data.errors) {
                        // var errorsAreas = $form.find('.errors');
                        $.each(data.errors, function (name, errors) {
                            validation.show($form, name, errors);
                        });
                    }
                    return null;
                }
            }
            toastr.error('Something went wrong...');
            return null;
        };

        /**
         * Parse response
         * @param {Object|XMLHttpRequest} data
         * @returns {Object|*}
         */
        return function(data) {
            if (data) {
                /** @typedef {{responseJSON, abort}} data */
                // Get response from XHR object
                if (data.responseJSON && data.abort && typeof data.abort === 'function') {
                    if (data.status == 401 && app.models.user.isLogged()) {
                        app.models.user.revokeAccess();
                    }
                    return handle(data.responseJSON);
                } else {
                    return handle(data);
                }
            } else {
                reCaptcha.render();
                toastr.error('Something went wrong...');
                return null;
            }
        };
    }) ();

    /**
     * Application
     */
    var app = {
        proto: {
            models: {},
            views: {}
        },
        models: {},
        views: {}
    };

    /**
     * Define login form
     * @type {Backbone.View|*}
     */
    app.proto.views.LoginForm = Backbone.View.extend({

        el: '#content',
        template: '#login-page',

        render: function () {
            $(this.el).html(_.template($(this.template).html()));
            reCaptcha.render();
            return this;
        },

        events: {
            'submit #login-form': 'submit'
        },

        submit: function (e) {
            e.preventDefault();

            if (reCaptcha.check()) {
                var data = $(e.currentTarget).serialize();
                app.models.user.login(data, function() {
                    app.router.navigate('index', { trigger: true });
                });
            }
        }
    });

    /**
     * Define registration form
     * @type {Backbone.View|*}
     */
    app.proto.views.RegisterForm = Backbone.View.extend({

        el: '#content',
        template: '#register-page',

        render: function () {
            $(this.el).html(_.template($(this.template).html()));
            reCaptcha.render();
            return this;
        },

        events: {
            'submit #register-form': 'submit'
        },

        submit: function (e) {
            e.preventDefault();

            if (reCaptcha.check()) {
                var data = $(e.currentTarget).serialize();
                app.models.user.register(data, function() {
                    app.router.navigate('login', { trigger: true });
                });
            }
        }
    });

    /**
     * Define index view
     * @type {Backbone.View|*}
     */
    app.proto.views.IndexView = Backbone.View.extend({

        el: '#content',
        content: null,

        render: function () {
            var self = this;
            $(self.el).html('');
            app.models.content.index(null, function(res) {
                $(self.el).html(res['content']);
            });
            return this;
        }
    });

    /**
     * Content model
     * @type {Backbone.Model|*}
     */
    app.proto.models.Content = Backbone.Model.extend({

        urlRoot: '',

        /**
         * Returns base url
         * @returns {string}
         */
        url: function () {
            return this.urlRoot;
        },

        /**
         * Index action
         * @param {String|Object|*} args
         * @param {Function} [next]
         * @returns {*}
         */
        index: function(args, next) {
            var url = this.url() + '/index/index',
                options = {
                    url: url,
                    type: 'GET',
                    headers: {
                        'Authorization': app.models.user.getAuthorizationHeader()
                    },
                    success: function (res) {
                        res = checkResponse(res);
                        res && next && next(res);
                    },
                    error: function (xhr) {
                        checkResponse(xhr);
                    }
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        }
    });

    /**
     * User model
     * @type {Backbone.Model|*}
     */
    app.proto.models.User = Backbone.Model.extend({

        urlRoot: '',
        data: null,
        access_token: null,
        logged: false,

        /**
         * Initialize user, loggin using localStorage
         */
        initialize: function() {
            var token = Storage.get('access_token');
            if (token) {
                this.access_token = token;
                this.logged = true;
                app.router.navigate('index', { trigger: true });
            }
        },

        /**
         * Returns base url
         * @returns {string}
         */
        url: function () {
            return this.urlRoot;
        },

        /**
         * Whether user is logged
         * @returns {boolean}
         */
        isLogged: function () {
            return this.logged;
        },

        /**
         * Revoke access (when session is expired)
         */
        revokeAccess: function () {
            Storage.remove('access_token');
            this.data = null;
            this.access_token = null;
            this.logged = false;
            app.router.navigate('login', { trigger: true });
        },

        /**
         * Get OAuth2 auth header
         * @returns {String|*}
         */
        getAuthorizationHeader: function () {
            return this.logged ? this.access_token['token_type'] + ' ' + this.access_token['access_token'] : null;
        },

        /**
         * Registration action
         * @param {String|Object} data
         * @param {Function} [next]
         * @returns {*}
         */
        register: function(data, next) {
            var url = this.url() + '/auth/register',
                options = {
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function (res) {
                        if (checkResponse(res)) {
                            next && next();
                        }
                    },
                    error: function (xhr) {
                        checkResponse(xhr);
                    }
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        },

        /**
         * Login action
         * @param {String|Object} data
         * @param {Function} [next]
         * @returns {*}
         */
        login: function(data, next) {
            var self = this;
            var url = this.url() + '/auth/login',
                options = {
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function (res) {
                        if (checkResponse(res)) {
                            Storage.set('access_token', res.access_token);
                            self.access_token = res.access_token;
                            self.logged = true;
                            next && next();
                        }
                    },
                    error: function(xhr) {
                        checkResponse(xhr);
                    }
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        },

        /**
         * Logout action
         * @param {String|Object} [args]
         * @param {Function} [next]
         * @returns {*}
         */
        logout: function(args, next) {
            var url = this.url() + '/auth/logout',
                options = {
                    url: url,
                    type: 'POST',
                    headers: {
                        'Authorization': app.models.user.getAuthorizationHeader()
                    },
                    success: function (res) {
                        checkResponse(res);
                        app.models.user.revokeAccess();
                        next && next();
                    },
                    error: function (xhr) {
                        checkResponse(xhr);
                        app.models.user.revokeAccess();
                    }
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        }
    });

    /**
     * Router
     * @type {Backbone.Router|*}
     */
    app.proto.Router = Backbone.Router.extend({
        loading: false,
        routes: {
            '*actions': 'defaultRoute'
        }
    });

    // Initialize the router
    app.router = new app.proto.Router();

    // Attach router handler
    app.router.on('route:defaultRoute', function(action) {

        if (app.models.user.isLogged()) {
            if (action == 'logout') {
                app.models.user.logout();
            }
            // else if (action == 'index') {
            //     app.views.indexView.render();
            // }
            else {
                app.router.navigate('index'/*, { trigger: true }*/);
                app.views.indexView.render();
            }
        } else {
            if (action == 'register') {
                app.views.registerForm.render();
            }
            // else if (action == 'login') {
            //     app.views.loginForm.render();
            // }
            else {
                app.router.navigate('login'/*, { trigger: true }*/);
                app.views.loginForm.render();
            }
        }
    });

    // Initialize user model
    app.models.user = new app.proto.models.User();

    // Initialize content model
    app.models.content = new app.proto.models.Content();

    // Initialize login form view
    app.views.loginForm = new app.proto.views.LoginForm();

    // Initialize register form view
    app.views.registerForm = new app.proto.views.RegisterForm();

    // Initialize index page view
    app.views.indexView = new app.proto.views.IndexView();

    // Start Backbone history
    Backbone.history.start();

}) (jQuery, Backbone, toastr, window);
