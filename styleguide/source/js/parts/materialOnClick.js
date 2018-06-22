/* global LightenColor, isSmartPhoneSize */
(function() {
    "use strict";
    $(document).ready(function () {
        /**
         * ===============================================
         * material-click effect
         * ===============================================
         */
        var _uniqueEventId = 0;

        /**
         * Important! The assigned effect needs an background
         * @param e Click event (offsets)
         * @param _this Object that clioked (as jQuery!)
         */
        function materialClick(e, _this) {
            var _e = _this;

            var _effect = _e.find('.material-click').first();
            var _offset = _e.offset();

            if (!_effect.length) {
                var _baseColor = _e.css('background-color');
                var _effectColor = _baseColor.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

                delete (_effectColor[0]);
                for (var i = 1; i <= 3; ++i) {
                    _effectColor[i] = parseInt(_effectColor[i]).toString(16);
                    if (_effectColor[i].length === 1){
                        _effectColor[i] = '0' + _effectColor[i];
                    }
                }
                _effectColor = LightenColor(_effectColor.join('').toUpperCase(), 15);

                _e.attr('data-material-effect-id', _uniqueEventId);
                _e.html('<div class="material-click-content" style="display:inline;">' + _e.html() + '</div>');
                _e.prepend('<div class="material-click" id="material-effect-' + _uniqueEventId + '"></div>');

                $('<style type="text/css">#material-effect-' + _uniqueEventId + '{ background-color:#' + _effectColor + ';} </style>').appendTo('head');
                _effect = _e.find('.material-click').first();

            }
            else {
                _effect.removeClass("animate");
            }
            // Don't set the size once!
            // UI is responsive, so the circle can change always
            var _size = Math.sqrt(_e.outerWidth() * _e.outerWidth() + _e.outerHeight() * _e.outerHeight()) * 2;
            _effect.css({height: _size, width: _size});


            var posX = e.pageX - _offset.left - _size / 2;
            var posY = e.pageY - _offset.top - _size / 2;

            _effect.css({top: posY + 'px', left: posX + 'px'}).addClass('animate');
            _uniqueEventId++;

            // Safari Hack
            if(_this.is('a') && _this.attr('href') !== undefined && _this.attr('href') !== ''){
                if (navigator.userAgent.indexOf('Safari') !== -1) {
                    e.preventDefault();
                    window.setTimeout(function () {
                        window.location = _this.attr('href');
                    }, 1);
                }
            }
            else{
                if(_this.parent().is('label')){
                    var _label = _this.parent();
                    $('input#'+_label.attr('for')).click();
                }
            }


        }

        // All Buttons with this class get the effect
        $('.material-click-action').click(function (e) {
            materialClick(e, $(this));
        });
        $('.material-click-action.material-click-small-only').click(function (e) {
            if (isSmartPhoneSize()) {
                materialClick(e, $(this));
            }
        });
    });
})();