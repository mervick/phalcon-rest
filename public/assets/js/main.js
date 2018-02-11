/**
 *
 * @author      {@link https://socialveo.com Socialveo}
 * @copyright   Copyright (C) 2018 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2018, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 */

;(function($, Backbone, toastr, win, undefined) {
    'use strict';

    // Init toastr options
    toastr.options = {
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": true,
    };

    // Init slide up/down alert notice
    $(document).on("click", '.alert-notice', function() {
        var self = $(this),
            group = self.closest(".form-group"),
            alert = group.find(".alert");

        if (alert.is(":hidden")) {
            alert.slideDown();
        } else {
            alert.slideUp();
        }
    });

    // Clear validations when value was changed
    $(document).on("change", '.has-errors input', function() {
        $(this).closest(".has-errors").removeClass("has-errors");
    });

    /**
     * Local storage
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
                'set': function (key, val) {
                    if (val === undefined) {
                        return self.remove(key);
                    }
                    storage.setItem(key, JSON.stringify(val));
                    return val;
                },
                'get': function (key, defaultVal) {
                    var val = self.parse(storage.getItem(key));
                    return (val === undefined ? defaultVal : val);
                },
                remove: function (key) {
                    storage.removeItem(key);
                },
                clear: function () {
                    storage.clear();
                },
                forEach: function (callback) {
                    for (var i = 0; i < storage.length; i++) {
                        var key = storage.key(i);
                        callback(key, self.get(key));
                    }
                },
                getAll: function () {
                    var ret = {};
                    self.forEach(function (key, val) {
                        ret[key] = val;
                    })
                    return ret;
                }
            });

            try {
                var testKey = '__STORAGE__'
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

        var render = function() {
            if (!loaded || !$('#' + container)[0]) return;
            var el = $("#" + container).html("");
            $("<div/>").insertAfter(el).attr("id", container);
            el.remove();
            reCaptchaID = window.grecaptcha.render(container, {
                'sitekey': sitekey,
            });
            return reCaptchaID;
        };

        window.reCaptchaCallback = function() {
            loaded = true;
            render();
        };

        return {
            /**
             * Render reCaptcha
             */
            render: render,

            /**
             * Check whether reCaptcha passed validation
             * @returns {boolean}
             */
            check: function() {
                if (!loaded || !window.grecaptcha || reCaptchaID === null) {
                    return false;
                }
                var response = grecaptcha.getResponse(reCaptchaID);

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

    var validation = {};

    /**
     * Show validation errors under the field
     * @param {jQuery} parent
     * @param {String} name
     * @param {Array} errors
     */
    validation.show = function(parent, name, errors) {
        if (errors && errors.length) {
            var container = parent.find(".errors").filter('[data-field=' + name + ']').html("");
            $.each(errors, function (i, error) {
                container.append(
                    '<p><small>' + error + '</small></p>'
                );
            });
            container.closest(".form-group").addClass("has-errors");
        }
    };

    /**
     * Hide validation errors under the field
     * @param {jQuery} form
     * @param {String} [name]
     */
    validation.hide = function(parent, name) {
        if (name) {
            parent.find(".errors").filter('[data-field=' + name + ']').html("")
                .closest(".has-errors").removeClass("has-errors");
        } else {
            parent.find(".has-errors").removeClass("has-errors");
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
            var $form = $("#content").find('form');

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

                    // Highlights fields errros
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

        // Parse response
        return function(data) {
            if (data) {
                // Get response from XHR object
                if (data.responseJSON && data.abort && typeof data.abort === 'function') {
                    if (data.status = 401 && user.isLogged()) {
                        user.revokeAccess();
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

    var views = {};

    /**
     * Define login form
     * @type {Backbone.View|*}
     */
    views.LoginForm = Backbone.View.extend({
        el: '#content',
        template: '#login-page',

        render: function () {
            $(this.el).html(_.template($(this.template).html()));
            reCaptcha.render();
            return this;
        },

        events: {
            'submit #login-form': 'submit',
        },

        submit: function (e) {
            e.preventDefault();

            if (reCaptcha.check()) {
                var data = $(e.currentTarget).serialize();
                user.login(data, function() {
                    router.navigate('index', { trigger: true });
                });
            }
        }
    });

    /**
     * Define registration form
     * @type {Backbone.View|*}
     */
    views.RegisterForm = Backbone.View.extend({
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
                user.register(data, function() {
                    router.navigate('login', { trigger: true });
                });
            }
        }
    });

    /**
     * Define index view
     * @type {Backbone.View|*}
     */
    views.IndexView = Backbone.View.extend({
        el: '#content',
        content: null,

        render: function () {
            var self = this;
            $(self.el).html('');
            content.index(null, function(res) {
                $(self.el).html(res['content']);
            });
            return this;
        }
    });

    /**
     * Content model
     * @type {Backbone.Model|*}
     */
    var Content = Backbone.Model.extend({

        urlRoot: "",

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
            // console.log({'Authorization' :user.getAuthorizationHeader()})
            var url = this.url() + '/index/index',
                options = {
                    url: url,
                    type: 'GET',
                    headers: {
                        'Authorization': user.getAuthorizationHeader()
                    },
                    success: function (res) {
                        res = checkResponse(res);
                        res && next && next(res);
                    },
                    error: function (xhr) {
                        checkResponse(xhr);
                    },
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        },
    });

    /**
     * User model
     * @type {Backbone.Model|*}
     */
    var User = Backbone.Model.extend({

        urlRoot: "",
        data: null,
        access_token: null,
        logged: false,

        /**
         * Initialize user, loggin using localStorage
         */
        initialize: function() {
            var token = Storage.get('access_token');
            if (token) {
                // console.log(this.access_token)
                this.access_token = token;
                this.logged = true;
                router.navigate('index', { trigger: true });
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
            router.navigate('login', { trigger: true });
        },

        /**
         * Get user fields
         * @returns {null}
         */
        getUserData: function () {
            return this.data;
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
                    },
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
            if (this.loading) return;
            this.loading = true;
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
                        this.loading = false;
                    },
                    error: function() {
                        checkResponse();
                        this.loading = false;
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
                        'Authorization': user.getAuthorizationHeader()
                    },
                    success: function (res) {
                        checkResponse(res);
                        user.revokeAccess();
                    },
                    error: function (xhr) {
                        checkResponse(xhr);
                        user.revokeAccess();
                    },
                };

            return (this.sync || Backbone.sync).call(this, null, this, options);
        }
    });

    /**
     * Router
     * @type {Backbone.Router|*}
     */
    var Router = Backbone.Router.extend({
        loading: false,
        routes: {
            '*actions': 'defaultRoute'
        }
    });

    // Initialize the router
    var router = new Router;

    router.on('route:defaultRoute', function(action) {

        // if (this.loading) return;
        // this.loading = true;

        if (user.isLogged()) {
            if (action == 'logout') {
                user.logout();
            }
            // else if (action == 'index') {
            //     var view = new views.IndexView();
            //     view.render();
            // }
            else {
                router.navigate('index'/*, { trigger: true }*/);
                var view = new views.IndexView();
                view.render();
            }
        } else {
            if (action == 'register') {
                var view = new views.RegisterForm();
                view.render();
            }
            // else if (action == 'login') {
            //     var view = new views.LoginForm();
            //     view.render();
            // }
            else {
                router.navigate('login'/*, { trigger: true }*/);
                var view = new views.LoginForm();
                view.render();
            }
        }
    });

    // Initialize user model
    var user = new User();

    // Initialize content model
    var content = new Content();

    // Start Backbone history
    Backbone.history.start();

}) (jQuery, Backbone, toastr, window);
