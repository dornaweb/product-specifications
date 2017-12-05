/**
 * Modal
 * Modified a little
 * @link https://github.com/JasonMHasperhoven/MultiModal
*/
if( typeof( $ ) == 'undefined' ) var $ = jQuery;

var Modal = (function () {
    function Modal(props) {
        this.props = props;
        this.className = '';
    }
    Modal.prototype.getJQueryElement = function () {
        this.HTMLButtons = this.renderButtons();
        this.HTMLActions = this.renderActions();
        this.HTMLMain = this.renderMain();
        this.HTMLHeader = this.renderHeader();
        this.HTMLCloseButton = this.renderCloseButton();
        this.HTMLModal = this.renderModal();
        if (this.props.fullScreen) {
            this.HTMLModal = this.renderFullScreenModal();
        }
        return $($.parseHTML(this.HTMLModal));
    };
    Modal.prototype.renderFullScreenModal = function () {
        return "<div class=\"modal__fullscreen\">\n      " + this.HTMLModal + "\n    </div>";
    };
    Modal.prototype.renderModal = function () {
        this.className = this.parseClass(this.props);
        if (this.props.fullScreen) {
            if (this.props.isIEBrowser) {
                return this.HTMLCloseButton + "\n          <div class=\"modal--ie " + this.className + " js-modal\">\n            " + this.HTMLHeader + "\n            " + this.HTMLMain + "\n          </div>";
            }
            else {
                return this.HTMLCloseButton + "\n          <div class=\"multi-modal__table\">\n            <div class=\"multi-modal__table-cell\">\n              <div class=\"modal " + this.className + " js-modal\">\n                " + this.HTMLHeader + "\n                " + this.HTMLMain + "\n              </div>\n            </div>\n          </div>";
            }
        }
        else {
            if (this.props.isIEBrowser) {
                return "<div class=\"modal--ie " + this.className + " js-modal\">\n          " + this.HTMLCloseButton + "\n          " + this.HTMLHeader + "\n          " + this.HTMLMain + "\n        </div>";
            }
            else {
                return "<div class=\"multi-modal__table\">\n          <div class=\"multi-modal__table-cell\">\n            <div class=\"modal " + this.className + " js-modal\">\n              " + this.HTMLCloseButton + "\n              " + this.HTMLHeader + "\n              " + this.HTMLMain + "\n            </div>\n          </div>\n        </div>";
            }
        }
    };
    Modal.prototype.renderCloseButton = function () {
        var HTMLCloseButton = '';
        if (this.props.closeButton || this.props.fullScreen) {
            var className = '';
            if (this.props.closeButton === 'mobile') {
                className = 'modal__close--mobile';
            }
            if (this.props.fullScreen) {
                className = 'modal__close--fullscreen';
            }
            HTMLCloseButton = "<div class=\"modal__close " + className + " js-modal-close\">&times;</div>";
        }
        return HTMLCloseButton;
    };
    Modal.prototype.renderHeader = function () {
        return "<div class=\"modal__header\">\n      " + this.props.title + "\n    </div>";
    };
    Modal.prototype.renderMain = function () {
        if (this.props.content.substring(0, 1) == '<') {
            this.content = this.props.content;
        }
        else {
            this.content = "\n        " + this.props.content + "\n";
        }
        return "<div class=\"modal__main\">\n      <div class=\"modal__content\">\n        " + this.content + "\n      </div>\n      " + this.HTMLActions + "\n    </div>";
    };
    Modal.prototype.renderActions = function () {
        return this.props.buttons ? "<div class=\"modal__actions\">\n      " + this.HTMLButtons + "\n    </div>" : '';
    };
    Modal.prototype.renderButtons = function () {
        var _this = this;
        var HTMLButtons = '';
        if (this.props.buttons) {
            Object.keys(this.props.buttons).forEach(function (key) {
                var href = '';
                var className = "modal__button " + _this.parseClass(_this.props.buttons[key]);
                if (_this.props.buttons[key].href) {
                    href = "href=\"" + _this.props.buttons[key].href + "\"";
                }
                if (_this.props.buttons[key].closeOnClick) {
                    className += ' js-modal-close';
                }
                HTMLButtons += "<a role=\"button\" " + href + " class=\"" + className + "\">\n          " + _this.props.buttons[key].value + "\n        </a>";
            });
        }
        return HTMLButtons;
    };
    Modal.prototype.parseClass = function (props) {
        var className = '';
        if (props.classList) {
            className += props.classList.join(' ');
        }
        if (props.className) {
            if (props.classList) {
                className += ' ';
            }
            className += props.className;
        }
        return className.trim();
    };
    return Modal;
})();
var MultiModal = (function () {
    function MultiModal(props) {
        this.props = props;
        this.modals = [];
        this.remote = { url: '', data: {} };
        this.isAnimating = false;
        this.props.isIEBrowser = this.getIEBrowser();
        if (!this.props.closeButton) {
            this.props.closeButton = 'mobile';
        }
    }
    MultiModal.prototype.new = function (props) {
        var _this = this;
        if (this.isAnimating)
            return;
        this.isAnimating = true;
        var mergedProps = $.extend(true, {}, this.props, props);
        if (!this.modals.length) {
            $(document.documentElement).css('overflow', 'hidden');
            $(document.body).append(this.renderMultiModal());
            this.$wrapper = $('.js-modal-wrapper');
            this.$backdrop = $('.js-modal-backdrop');
            this.$backdrop.click(function () { return _this.onBackdropClick(); });
        }
        if (mergedProps.remote) {
            this.appendRemote(mergedProps);
        }
        else {
            this.appendModal(new Modal(mergedProps).getJQueryElement(), mergedProps);
        }
    };
    MultiModal.prototype.appendRemote = function (props) {
        var _this = this;
        this.remote.url = props.remote;
        this.remote.data = {};
        if (typeof props.remote === 'object') {
            this.remote.url = props.remote.url;
            this.remote.data = props.remote.data;
        }
        var request = $.get(this.remote.url, this.remote.data);
        request.done(function (HTMLRemote) {
            var $remote = $($.parseHTML("<div class=\"modal--remote js-modal\">\n        " + HTMLRemote + "\n      </div>", null, true));
            _this.appendModal($remote, props);
        });
        request.fail(function (http) {
            if (http.status === 404) {
                throw new Error("MultiModal::FileNotFound: " + this.remote.url);
            }
            else if (http.status === 500) {
                throw new Error("MultiModal::InternalServerError: " + this.remote.url);
            }
        });
    };
    MultiModal.prototype.appendModal = function ($element, props) {
        var _this = this;
        this.modals.push({
            $wrapper: $element,
            allowBackdropClose: typeof props.allowBackdropClose === 'boolean' ? props.allowBackdropClose : true
        });
        this.currentModal = this.modals.length - 1;
        this.$wrapper.append(this.modals[this.currentModal].$wrapper);
        this.modals[this.currentModal].$modal = $('.js-modal').last();
        if (props.isIEBrowser || props.remote) {
            this.centerModal();
        }
        var Timeline = new TimelineLite({
            onComplete: function () { _this.isAnimating = false; }
        });
        this.animateInNewModal(Timeline);
        this.animateInOtherModals(Timeline);
        this.fadeInBackdrop();
        if (props.fullScreen) {
            this.fadeInFullscreen();
        }
    };
    MultiModal.prototype.close = function () {
        var _this = this;
        if (this.isAnimating)
            return;
        this.isAnimating = true;
        if (this.modals.length == 1) {
            this.$backdrop.removeClass('is-active');
        }
        var Timeline = new TimelineLite({
            onComplete: function () { return _this.onCloseComplete(); }
        });
        this.animateOutCurrentModal(Timeline);
        this.animateOutOtherModals(Timeline);
    };
    MultiModal.prototype.closeAll = function () {
        var _this = this;
        this.$backdrop.removeClass('is-active');
        var Timeline = new TimelineLite({
            onComplete: function () { return _this.onCloseAllComplete(); }
        });
        this.animateOutAllModals(Timeline);
    };
    MultiModal.prototype.onBackdropClick = function () {
        if (this.modals[this.currentModal].allowBackdropClose) {
            this.close();
        }
    };
    MultiModal.prototype.renderMultiModal = function () {
        return "<div class=\"multi-modal__wrapper js-modal-wrapper\">\n      <div class=\"multi-modal__backdrop js-modal-backdrop\"></div>\n    </div>";
    };
    MultiModal.prototype.getIEBrowser = function () {
        var ua = window.navigator.userAgent;
        var msIE = ~ua.indexOf('MSIE ') || ~ua.indexOf('Trident/');
        return !!msIE;
    };
    MultiModal.prototype.animateInNewModal = function (Timeline) {
        Timeline.set(this.modals[this.currentModal].$modal, {
            display: 'block',
            opacity: 0,
            scale: .5
        }).to(this.modals[this.currentModal].$modal, .5, {
            opacity: 1,
            scale: 1,
            ease: Power4.easeInOut
        }, 'in');
    };
    MultiModal.prototype.animateInOtherModals = function (Timeline) {
        if (this.modals.length > 1) {
            if (this.props.fullScreen) {
                Timeline.to(this.modals[this.currentModal - 1].$modal, .5, {
                    scale: '-=.15',
                    opacity: 0,
                    ease: Power4.easeInOut
                }, 'in');
            }
            else {
                for (var i in this.modals) {
                    if (!(parseInt(i) === this.currentModal || parseInt(i) < (this.currentModal - 4))) {
                        Timeline.to(this.modals[i].$modal, .5, {
                            y: '-=24',
                            scale: '-=.15',
                            opacity: '-=.25',
                            ease: Power4.easeInOut
                        }, 'in');
                    }
                }
            }
        }
    };
    MultiModal.prototype.fadeInBackdrop = function () {
        this.$backdrop.addClass('is-active');
    };
    MultiModal.prototype.fadeInFullscreen = function () {
        this.modals[this.currentModal].$wrapper.addClass('is-visible');
    };
    MultiModal.prototype.centerModal = function () {
        this.modals[this.currentModal].$modal.css({
            position: 'absolute',
            left: '50%',
            top: '50%',
            marginTop: -(this.modals[this.currentModal].$modal.height() / 2),
            marginLeft: -(this.modals[this.currentModal].$modal.width() / 2)
        });
    };
    MultiModal.prototype.animateOutCurrentModal = function (Timeline) {
        Timeline.to(this.modals[this.currentModal].$modal, .32, {
            opacity: 0,
            scale: .5,
            ease: Power4.easeInOut
        }, 'out');
        if (this.props.fullScreen) {
            this.modals[this.currentModal].$wrapper.removeClass('is-visible');
        }
    };
    MultiModal.prototype.animateOutOtherModals = function (Timeline) {
        if (this.modals.length > 1) {
            if (this.props.fullScreen) {
                Timeline.to(this.modals[this.currentModal - 1].$modal, .5, {
                    scale: '+=.15',
                    opacity: 1,
                    ease: Power4.easeInOut
                }, 'out');
            }
            else {
                for (var i in this.modals) {
                    if (!(parseInt(i) === this.currentModal || parseInt(i) < (this.currentModal - 4))) {
                        Timeline.to(this.modals[i].$modal, .5, {
                            y: '+=24',
                            scale: '+=.15',
                            opacity: '+=.25',
                            ease: Power4.easeInOut
                        }, 'out');
                    }
                }
            }
        }
    };
    MultiModal.prototype.animateOutAllModals = function (Timeline) {
        for (var i in this.modals) {
            if (parseInt(i) > (this.currentModal - 4)) {
                Timeline.to(this.modals[i].$modal, .5, {
                    opacity: 0,
                    scale: '-=.5',
                    ease: Power4.easeInOut
                }, 'out');
            }
        }
        if (this.props.fullScreen) {
            for (var i in this.modals) {
                this.modals[i].$wrapper.removeClass('is-visible');
            }
        }
    };
    MultiModal.prototype.onCloseComplete = function () {
        if (this.props.isIEBrowser) {
            this.modals[this.currentModal].$modal.remove();
        }
        else {
            this.modals[this.currentModal].$wrapper.remove();
        }
        this.modals.splice(this.currentModal, 1);
        this.currentModal = this.currentModal - 1;
        if (!this.modals.length) {
            $(document.documentElement).css('overflow', '');
            this.$wrapper.remove();
        }
        this.isAnimating = false;
    };
    MultiModal.prototype.onCloseAllComplete = function () {
        if (this.props.isIEBrowser) {
            $('.js-modal').remove();
        }
        else {
            $('.js-modal-wrapper').remove();
        }
        this.modals = [];
        this.currentModal = -1;
        $(document.documentElement).css('overflow', '');
        this.$wrapper.remove();
        this.isAnimating = false;
    };
    return MultiModal;
})();