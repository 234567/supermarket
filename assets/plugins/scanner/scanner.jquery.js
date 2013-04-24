(function ($) {
    "use strict";

    $.scanner = function (options) {

        var opts = $.extend({}, $.scanner.defaults, options);
        if(!uniqueInstance) {
            uniqueInstance = init(opts);
        }
        return uniqueInstance;
    };



    var uniqueInstance,
        _container,
        _video,
        _canvas,
        _tips,
        _flash,
        _context,
        _overlay,
        _FPS = 60,
        _width,
        _height,
        _success,
        _delay,
        _stop = false;


    var _getUserMedia = (
        navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.oGetUserMedia ||
            navigator.msieGetUserMedia ||
            false
        );
    var _requestAnimationFrame = (
        window.requestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.msRequestAnimationFrame
        );


    //私有函数
    function init(opts) {
        _container = $(opts.container);
        _FPS = opts.cameraFPS || 60;
        _width = opts.cameraWidth || 320;
        _height = opts.cameraHeight || 240;
        _success = opts.success;
        _delay = opts.scanDelay;


        //创建遮罩层
        _overlay = $('<div></div>').addClass('overlay').css({
            background: opts.color,
            opacity: opts.opacity,
            top: opts.container.toString() == 'body' ? $(opts.container).scrollTop() : $(opts.container).offset().top,
            left: $(opts.container).offset().left,
            width: opts.container === 'body' ? '100%' : $(opts.container).width(),
            height: opts.container === 'body' ? '100%' : $(opts.container).height(),
            position: 'absolute',
            zIndex: 99998,
            display: 'none',
            overflow: 'hidden'
        });


        //创建一个隐藏的VIDEO。
        _video = $('<video></video>').attr({
            autoplay: true,
            width: opts.cameraWidth,
            height: opts.cameraHeight
        }).css('display', 'none').appendTo(_overlay);

        _canvas = $('<canvas></canvas>').attr({
            id: "scanner-canvas",
            width: opts.cameraWidth,
            height: opts.cameraHeight
        }).css({
                width: opts.cameraWidth,
                height: opts.cameraHeight,
                left: ($(window).width()-_width)/2,
                top: 50,
                opacity:1,
                position: 'absolute',
                zIndex:99999,
                background:"#000",
                //border:"px solid #000"
                borderRadius:"5px"
            }).appendTo(_overlay);
        _context = _canvas[0].getContext("2d");

        _tips = $('<div></div>').attr({
            id:"scanner-tips"
        }).css({
                width: opts.cameraWidth,
                height: 60,
                left: ($(window).width()-_width)/2,
                top: 50+opts.cameraHeight,
                position: 'absolute',
                background:"#666 "
            }).html("请将条形码放到摄像头前进行扫描<a class=\"btn btn-mini scanner-close\">关闭扫描器</a><br/><span class=\"label\">当前识别的条形码：</span>").appendTo(_overlay);
        $('a.scanner-close').live('click',function(){
            $(_overlay).hide();
            //_video[0].pause();
            _stop = true;
            _container.css('overflow', 'auto');
        });

        //浏览器支持
        if (_getUserMedia) {
            _requestAnimationFrame(function () {
                //获取摄像头内容
                _getUserMedia.call(navigator, {"video": true }, onStream, onStreamError);
            });
        } else {
            useFlash();
        }

        _container.append(_overlay);


        return {
            show:function () {
                $(_overlay).show();
                //_video[0].play();
                _stop = false;
                setTimeout(onCapture, 1000 / _FPS);
                _container.css('overflow', 'hidden');
            },
            pause:function(){
                //_video[0].pause();
                _stop = true;
            },
            close:function () {
                $(_overlay).hide();
                //_video[0].pause();
                _stop = true;
                _container.css('overflow', 'auto');
            }
        };
    }

    function onStream(stream) {
        _video.attr('src',  ( window.URL || window.webkitURL ).createObjectURL(stream));
        //开始捕获图像进行解析
        setTimeout(onCapture, 1000 / _FPS);
    }

    function onStreamError(error) {
        //如果是被拒绝，继续请求许可
        if (error.PERMISSION_DENIED) {
            alert(error + "\n请允许浏览器使用你的摄像头～");
            _requestAnimationFrame(function () {
                //获取摄像头内容
                _getUserMedia.call(navigator, {"video": true }, onStream, onStreamError);
            });
        } else {
            //既然这样，用FLASH吧
            useFlash();
        }
    }


    function onCapture() {
        //var ctx = document.getElementById("test").getContext("2d");
        //ctx.drawImage(_video[0], 0, 0, _width, _height);
        if(_stop){
            return ;
        }
        _context.drawImage(_video[0], 0, 0, _width, _height);
        var barcode = new Barcode(_context, _width, _height);
        var line = barcode.scan();
        if (line) {
            barcode.print(line);
            _success(line.isbn);
            //延迟扫描下一次
            setTimeout(onCapture, _delay);
        } else {
            setTimeout(onCapture, 1000 / _FPS);
        }
    }

    function useFlash() {
        //采用FLASH方式
        var flash = document.createElement("object");
        flash.classid = "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000";
        flash.id = "eanreader";
        flash.width = 320;
        flash.height = 240;
        flash.align = "middle";

        flash.innerHTML = '<param name="movie"   value="EANReader.swf"/>' +
            '<param name="quality" value="high"    />' +
            '<param name="scale"   value="noscale" />' +
            'param name="salign"  value="lt"      />' +
            '<param name="bgcolor" value="#000000" />' +
            '<param name="allowScriptAccess" value="always"/>' +
            'embed src     = "EANReader.swf" name    = "eanreader" bgcolor = "#000000"' +
            'width   = "320" height  = "240" quality = "high" align   = "middle" type    = "application/x-shockwave-flash"' +
            'allowScriptAccess = "always" pluginspage       = "http://www.macromedia.com/go/getflashplayer"/>';

        $(_overlay).append(flash);
        //FLASH的回调函数
        window.scan_success = function (code) {
            _success(code);
        }
    }


    //Barcode 对象封装来源：https://github.com/liip/barcode.js
    //TODO：针对摄像头的模糊解析，此处还没有进行完善。
    //感谢作者的开源，

    var Barcode = function (context, width, height) {
        this.context = context;
        this.width = width;
        this.height = height;
    };

    Barcode.MAX_VARIANCE_SEPARATOR = 0.9;
    Barcode.MAX_VARIANCE = 0.27;

    Barcode.prototype.scan = function () {

        var horizontal = 2;

        while (horizontal--) {

            var length = horizontal ? this.width : this.height;
            var scan = 10;
            var step = length / scan;

            while (scan--) {

                var xy = (step * scan * 1.5) % length;

                if (horizontal) {
                    var x = 0;
                    var y = xy;
                    var width = length;
                    var height = 1;
                } else {
                    var x = xy;
                    var y = 0;
                    var width = 1;
                    var height = length;
                }

                var data = this.context.getImageData(x, y, width, height).data;
                var grey = Barcode.grey(data);

                for (var contrast = 10; contrast <= 750; contrast += 10) {

                    var bits = Barcode.convert(grey, contrast);
                    var reverse = 2;
                    while (reverse--) {

                        var line = new Barcode.Line(bits, x, y, width, height, horizontal);

                        if (line.parse()) {
                            return line;
                        }

                        bits.reverse();
                    }
                }
            }
        }

        return false;
    };

    Barcode.prototype.print = function (line) {

        for (var i = 0; i < line.bits.length; i++) {

            this.context.fillStyle = 'rgb(' + line.bits[i] + ', ' + line.bits[i] + ', ' + line.bits[i] + ')';

            if (line.horizontal) {
                this.context.fillRect(i, line.y, 1, 100);
            } else {
                this.context.fillRect(line.x, i, 100, 1);
            }
        }

        this.context.fillStyle = 'rgba(255, 0, 0, 0.5)';
        if (line.horizontal) {
            this.context.fillRect(line.x, line.y, line.width, 5);
        } else {
            this.context.fillRect(line.x, line.y, 5, line.height);
        }
    };

    Barcode.grey = function (data) {

        var grey = [];

        for (var i = 0, n = data.length; i < n; i += 4) {
            grey[grey.length] = data[i] + data[i + 1] + data[i + 2];
        }

        return grey;
    };

    Barcode.convert = function (grey, contrast) {

        var bits = [];

        for (var i = 0, n = grey.length; i < n; i++) {
            bits[i] = grey[i] < contrast ? 0 : 255;
        }

        return bits;
    };

    Barcode.runlength = function (bits) {

        var lines = [];
        var current = bits[0];
        var count = 0;
        for (var col = 0; col < bits.length; col++) {
            if (bits[col] == current) {
                count++;
            } else {
                lines.push(count);
                count = 1;
                current = bits[col];
            }
        }
        lines.push(count);

        return lines;
    };

    Barcode.Line = function (bits, x, y, width, height, horizontal) {
        this.bits = bits;
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        this.horizontal = horizontal;

        this.start = 0;
        this.bar = 0;
        this.digits = [];

        this.isbn = '';
    };

    Barcode.Line.prototype.parse = function () {

        var lines = Barcode.runlength(this.bits);

        // find start
        var bar = 0, start = 0, end = 0;
        for (var i = 0; i < (lines.length - 3); i++) {

            var all = lines.slice(i, i + 59);

            var total = 0;
            for (var j = 0; j < all.length; j++) {
                total += all[j];
            }
            var bar = total / 95;

            var variance = (lines[i] / bar) * (lines[i + 1] / bar) * (lines[i + 2] / bar);

            if (Math.abs(1 - variance) < Barcode.MAX_VARIANCE_SEPARATOR) {

                // check middle
                var variance = (lines[i + 27] / bar) * (lines[i + 28] / bar) * (lines[i + 29] / bar) * (lines[i + 30] / bar) * (lines[i + 31] / bar);

                if (Math.abs(1 - variance) < Barcode.MAX_VARIANCE_SEPARATOR) {

                    // check end
                    var variance = (lines[i + 56] / bar) * (lines[i + 57] / bar) * (lines[i + 58] / bar);

                    if (Math.abs(1 - variance) < Barcode.MAX_VARIANCE_SEPARATOR) {

                        end = i + 59;
                        start = i + 3;

                        // start, middle and end found
                        break;
                    }
                }
            }
        }

        if (end == 0) {

            // no end found
            return false;
        }

        // decode barcode
        var GROUP = 6;

        var isbn = '';
        var sum = '';

        var bars = lines.slice(start, start + 4 * GROUP);
        bars = bars.concat(lines.slice(start + 4 * GROUP + 5, start + 4 * 2 * GROUP + 5));
        for (var i = 0; i < 2 * GROUP; i++) {

            var digits = [
                bars[i * 4],
                bars[i * 4 + 1],
                bars[i * 4 + 2],
                bars[i * 4 + 3]
            ];
            this.digits.push(digits);

            var pattern = Barcode.EAN13.match(digits, bar);
            if (pattern) {
                sum += 'L';
            } else {
                sum += 'G';
                pattern = Barcode.EAN13.match(digits.reverse(), bar);
            }

            if (!pattern) {
                // no pattern match found
                return false;
            }

            isbn += pattern;
        }

        var first = Barcode.EAN13.FIRST_DIGITS[sum.substr(0, 6)] || false;

        if (!first) {
            // no first pattern found
            return false;
        }

        this.isbn = first + isbn;

        return Barcode.EAN13.checksum(this.isbn);
    };

    Barcode.EAN13 = {
        PATTERNS: [
            [3, 2, 1, 1], // 0
            [2, 2, 2, 1], // 1
            [2, 1, 2, 2], // 2
            [1, 4, 1, 1], // 3
            [1, 1, 3, 2], // 4
            [1, 2, 3, 1], // 5
            [1, 1, 1, 4], // 6
            [1, 3, 1, 2], // 7
            [1, 2, 1, 3], // 8
            [3, 1, 1, 2]  // 9
        ],
        FIRST_DIGITS: {
            'LLLLLL': '0',
            'LLGLGG': '1',
            'LLGGLG': '2',
            'LLGGGL': '3',
            'LGLLGG': '4',
            'LGGLLG': '5',
            'LGGGLL': '6',
            'LGLGLG': '7',
            'LGLGGL': '8',
            'LGGLGL': '9'
        },
        checksum: function (isbn) {

            var length = isbn.length;
            var sum = 0;

            for (var i = 0; i < length; i++) {

                if (i % 2 == 0) {
                    sum += parseInt(isbn[i]);
                } else {
                    sum += parseInt(isbn[i]) * 3;
                }
            }

            return sum % 10 == 0;
        },
        match: function (digits, bar) {

            var best = Barcode.MAX_VARIANCE;
            var variance = 0;
            var match = false;

            for (var j = 0; j < this.PATTERNS.length; j++) {

                variance = this.variance(digits, this.PATTERNS[j], bar);

                if (variance < best) {
                    best = variance;
                    match = "" + j;
                }
            }

            return match;
        },
        variance: function (digits, pattern, bar) {

            var sum = digits[0] + digits[1] + digits[2] + digits[3];
            if (isNaN(sum)) {
                return 9999;
            }
            var total = 0;

            total += Math.abs(digits[0] - pattern[0] * bar);
            total += Math.abs(digits[1] - pattern[1] * bar);
            total += Math.abs(digits[2] - pattern[2] * bar);
            total += Math.abs(digits[3] - pattern[3] * bar);

            return total / sum;
        }
    };


    // 插件的defaults
    $.scanner.defaults = {
        //扫描模式，single表示单个扫描单个条码，multi，表示连续扫描模式
        scanType: 'single',
        codeType: 'ean-13',
        cameraWidth: 320,
        cameraHeight: 240,
        cameraFPS: 60,
        //扫描延迟时间
        scanDelay: 2000,
        success: null,
        //
        color: '#aaa',
        opacity: 0.85,
        container: 'body'
    };
})(jQuery);
