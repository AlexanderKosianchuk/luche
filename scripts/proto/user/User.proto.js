/*jslint browser: true*/
/*global $, jQuery*/

function User($window, document, langStr, eventHandler) {
    'use strict';

    var userId = null,
        userListWorkspace = null,
        userListContainer = null,
        userListContent = null,
        createUpdateUserContent = null,
        usersTable = null;

    this.userId = null;
    this.task = null;

    this.ResizeUserContainer = function () {
        eventHandler.trigger("resizeShowcase");
        return this;
    };

    this.logout = function () {
        var pV = {
            action : 'user/userLogout',
            data : {
                data : 'data'
            }
        };

        $.ajax({
            type : "POST",
            data : pV,
            dataType : 'json',
            url : ENTRY_URL,
            async : true
        }).done(function () {
            $('<form></form>')
                .css({display: 'none'})
                .attr('action', window.location.protocol + '//' + window.location.host)
                .attr('method', 'get')
                .appendTo('body')
                .submit();
        });
    };

    this.changeLanguage = function (lang) {
        var pV = {
            action : 'user/userChangeLanguage',
            data : {
                lang : lang
            }
        };

        $.ajax({
            type : "POST",
            data : pV,
            dataType : 'json',
            url : ENTRY_URL,
            async : true
        }).done(function () {
            $('<form></form>')
                .css({display: 'none'})
                .attr('action', window.location.protocol + '//' + window.location.host)
                .attr('method', 'get')
                .appendTo('body')
                .submit();
        });
    };

    this.FillFactoryContaider = function (userListWorkspace) {
        this.FillFactoryContaiderByUserList(userListWorkspace);
    };

    this.FillFactoryContaiderByUserList = function (userListWorkspace) {
        userId = this.userId;
        this.userListWorkspace = userListWorkspace;
        this.ShowUserViewOptions();
        this.ShowUserList();
    };

    this.ShowUserViewOptions = function () {
        var self = this;

        if (self.userListWorkspace !== null) {
            self.userListWorkspace
                    .append("<div id='userListOptions' class='OptionsMenu'></div>");
            self.userListOptions = $("div#userListOptions");

            var getButton = function (id, label) {
                return $('<div></div>')
                    .append(
                        $('<button></button>')
                            .attr('id', id)
                            .addClass('Button user-opitons-button')
                            .append(label)
                    );
            };

            var userOptions = $('<table></table')
                .attr('v-align', 'top')
                .append(
                    $('<tr></tr>')
                        .append(
                            $('<td></td>')
                                .append(
                                    $('<label></label>')
                                        .append(langStr.userActions)
                                        .append(' - ')
                            )
                        )
                        .append($('<td></td>').append(getButton('userOpitonsCreateButton', langStr.userAdd)))
                        .append($('<td></td>').append(getButton('userOpitonsEditButton', langStr.userEdit)))
                        .append($('<td></td>').append(getButton('userOpitonsDeleteButton', langStr.userDelete)))
                        .append($('<td></td>').append(getButton('userOpitonsSaveButton', langStr.userSave)))
                        .append($('<td></td>').append(getButton('userOpitonsCancelButton', langStr.userCancel)))
                    );

            self.userListOptions.append(userOptions);
            self.UserViewOptionsInitialState();
            self.NoUserCheckedViewOptionsState();
            self.BindButtonEvents();
        }
    };

    this.ShowUserList = function () {
        var self = this;

        if ((userListContent !== null) && ($('#userListContent').length > 0)) {
            $('.user-content').slideUp(function () {
                    userListContent.slideDown();
                    self.NoUserCheckedViewOptionsState();

                    $(".ItemsCheck").removeAttr("checked");
                    $(".ItemsCheck").prop("checked", false);
            });
        } else {
            var pV = {
                action : "user/buildUserTable",
                data : {
                    data : 'data'
                }
            };

            $.ajax({
                type : "POST",
                data : pV,
                dataType : 'json',
                url : ENTRY_URL,
                async : true
            })
            .done(function (answ) {
                if (answ["status"] == "ok") {
                    var userTable = answ['data'], sortCol = answ['sortCol'], sortType = answ['sortType'];
                    var append = function () {
                        self.userListWorkspace.append("<div id='userListContent' class='Content user-content is-scrollable'></div>");
                        userListContent = $('#userListContent');

                        userListContent
                            .append(userTable)
                            .slideDown();
                        self.SupportDataTable(sortCol, sortType);
                        self.NoUserCheckedViewOptionsState();
                        self.ResizeUserContainer();
                    }

                    if($('.user-content').length > 0) {
                        $('.user-content').slideUp(function () {
                            append();
                        });
                    } else {
                        append();
                    }
                } else {
                    console.log(answ["error"]);
                }
            });
        }
    }

    this.viewOptionsButtons;
    this.UserViewOptionsInitialState = function () {
        if (!this.viewOptionsButtons) {
            this.viewOptionsButtons = $('button.user-opitons-button');
        }
        $('button.user-opitons-button').button({
            disabled : true
        }).addClass('hidden');
    };

    this.NoUserCheckedViewOptionsState = function () {
        this.UserViewOptionsInitialState();

        $('button#userOpitonsCreateButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsEditButton').removeClass('hidden');
        $('button#userOpitonsDeleteButton').removeClass('hidden');
    };

    this.OneUserCheckedViewOptionsState = function () {
        this.UserViewOptionsInitialState();

        $('button#userOpitonsCreateButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsEditButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsDeleteButton').button({
            disabled : false
        }).removeClass('hidden');
    };

    this.ManyUserCheckedViewOptionsState = function () {
        this.UserViewOptionsInitialState();

        $('button#userOpitonsCreateButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsEditButton').removeClass('hidden');
        $('button#userOpitonsDeleteButton').button({
            disabled : false
        }).removeClass('hidden');
    };

    this.CreateOrUpdateUserViewOptionsState = function () {
        this.UserViewOptionsInitialState();

        $('button#userOpitonsListButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsSaveButton').button({
            disabled : false
        }).removeClass('hidden');
        $('button#userOpitonsCancelButton').button({
            disabled : false
        }).removeClass('hidden');
    };

    this.BindButtonEvents = function () {
        var self = this;
        $('button#userOpitonsCreateButton').on('click', function () {
            self.ShowCreateUserForm();
        });

        $('button#userOpitonsCancelButton').on('click', function () {
            self.ShowUserList();
            $("form#user-cru-form")[0].reset();
        });

        $('button#userOpitonsSaveButton').on('click', function () {
            self.UserSave().done(function (answ){
                userListContent.remove();
                userListContent = null;
                usersTable = null;
                self.ShowUserList();
                $(':input','#user-cru-form')
                 .not(':button, :submit, :reset, :hidden')
                 .val('')
                 .removeAttr('checked')
                 .removeAttr('selected');
            });
        });

        $('button#userOpitonsEditButton').on('click', function () {
            var userId = $('.ItemsCheck:checked').eq(0).data('userid');
            self.ShowUpdateUserForm(userId);
        });

        $('button#userOpitonsDeleteButton').on('click', function () {
            if (confirm(langStr.confimUserDeletion)) {
                var itemsChecked = $('.ItemsCheck:checked');
                var userIds = [];
                $.each(itemsChecked, function (index, item) {
                    userIds.push(parseInt($(item).data('userid')));
                });

                if(userIds.length > 0) {
                    self.UserDelete(userIds).done(function (answ){
                        userListContent.remove();
                        userListContent = null;
                        usersTable = null;
                        self.ShowUserList();
                    });
                }
            }
        });
    }

    this.SupportDataTable = function (sortColumn, sortType) {
        var self = this, sortType = sortType.toLowerCase();

        usersTable = $('#userTable').dataTable({
            "bInfo" : false,
            "bSort" : true,
            "aoColumnDefs" : [ {
                'bSortable' : false,
                'aTargets' : [ 0 ]
            }, {
                "sClass" : "UserCheckboxCenter",
                'aTargets' : [ 0 ]
            } ],
            "order" : [ [ sortColumn, sortType ] ],
            "bFilter" : false,
            "bLengthChange" : false,
            "bAutoWidth" : false,
            "bProcessing" : true,
            "bServerSide" : true,
            "aLengthMenu" : false,
            "bPaginate" : false,
            "sAjaxSource" : ENTRY_URL,
            "fnServerData" : function (sSource, aoData, fnCallback) {
                var pV = {
                    action : "user/segmentTable",
                    data : {
                        data : aoData
                    }
                };

                $.ajax({
                    "dataType" : 'json',
                    "type" : "POST",
                    "url" : sSource,
                    "data" : pV,
                    "success" : fnCallback
                }).done(function (a) {
                    self.SupportTableContent();
                }).fail(function (a) {
                    console.log(a);
                });
            },
            "oLanguage" : langStr.dataTable,
        });

        $("#tableCheckAllItems").on("click", function (e) {
            var el = $(e.target);

            if (el.attr("checked") == "checked") {
                $(".ItemsCheck").removeAttr("checked");
                $(".ItemsCheck").prop("checked", false);
                el.removeAttr("checked");
            } else {
                $(".ItemsCheck").attr("checked", "checked");
                $(".ItemsCheck").prop("checked", true);
                el.attr("checked", "checked");
            }
        });
    };

    this.SupportTableContent = function () {
        var self = this;
        $('.ItemsCheck, #tableCheckAllItems').on('click', function (e) {
            var itemsChecked = $('.ItemsCheck:checked');
            if (itemsChecked.length === 0) {
                self.NoUserCheckedViewOptionsState();
            } else if (itemsChecked.length === 1) {
                self.OneUserCheckedViewOptionsState();
            } else {
                self.ManyUserCheckedViewOptionsState();
            }
        });
    }

    this.ShowUserForm = function (action, data) {
        var self = this;

        if((createUpdateUserContent !== null)  && $('#createUpdateUserContent').length > 0) {
            $('.user-content').slideUp(function () {
                createUpdateUserContent.slideDown();
                self.CreateOrUpdateUserViewOptionsState();
            });
        } else {
            $.ajax({
                "dataType" : 'json',
                "type" : "POST",
                "data" : {
                    'action' : 'user/' + action,
                    'data' : data
                },
                "url" : ENTRY_URL,
                "async" : true
            }).done(function (html) {
                var append = function () {
                    self.userListWorkspace.append("<div id='createUpdateUserContent' class='Content user-content'></div>");
                    createUpdateUserContent = $('#createUpdateUserContent');

                    createUpdateUserContent
                        .append(html)
                        .slideDown();
                    self.CreateOrUpdateUserViewOptionsState();
                    self.ResizeUserContainer();
                }

                if($('.user-content').length > 0) {
                    $('.user-content').slideUp(function () {
                        append();
                    });
                } else {
                    append();
                }
            }).fail(function (a) {
                console.log(a);
            });
        }

        return true;
    };

    this.ShowCreateUserForm = function () {
        return this.ShowUserForm('createUserForm', {"data": 'dummy'});
    };

    this.ShowUpdateUserForm = function ($id) {
        return this.ShowUserForm('updateUserForm', {"userid": $id});
    }

    this.UserSave = function () {
        var dfd = $.Deferred();
        $('form#user-cru-form')
            .one('submit', function (e){
                dfd = $.ajax({
                    url: ENTRY_URL,
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (a) {
                        var answ = $.parseJSON(a);
                        if((answ['status'] == 'err') && answ['error'].length > 0) {
                            $('.user-creation-info').eq(0).find('p').text(answ['error']);
                        } else {
                            $('.user-creation-info').eq(0).find('p').text('');
                        }
                    },
                    fail: function (a) {
                        $('.user-creation-info').eq(0).find('p').text(langStr.userCreaitonFailServerError);
                    },
                });
                e.preventDefault();
            }).submit();
        return dfd;
    };

    this.UserDelete = function (userIds) {
        return $.ajax({
            "dataType" : 'json',
            "type" : "POST",
            "data" : {
                'action' : "user/deleteUser",
                'data' : {
                    'userIds' : userIds
                }
            },
            "url" : ENTRY_URL,
        });
    };
}
