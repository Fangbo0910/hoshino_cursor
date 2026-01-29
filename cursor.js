(function () {
    // 初始化前确保设置对象存在。
    if (typeof HoshinoCursorSettings === 'undefined') {
        return;
    }

    var settings = HoshinoCursorSettings;

    // 触屏设备禁用自定义光标。
    if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
        return;
    }

    var body = document.body;
    body.classList.add('hoshino-cursor-active');

    var mainEnabled = !!settings.mainEnabled;
    var inputEnabled = !!settings.inputEnabled;
    var mainSvg = settings.mainSvg || '';
    var inputSvg = settings.inputSvg || '';

    // 未启用任何光标或未配置 SVG 时直接退出。
    if ((!mainEnabled && !inputEnabled) || (!mainSvg && !inputSvg)) {
        body.classList.remove('hoshino-cursor-active');
        return;
    }

    // 设置光标尺寸与粒子样式变量。
    var cursorSize = clamp(settings.cursorSize || 32, 24, 40);
    document.documentElement.style.setProperty('--hoshino-cursor-size', cursorSize + 'px');
    var particleColor = settings.particleColor || '#ff5fa2';
    document.documentElement.style.setProperty('--hoshino-particle-color', particleColor);
    document.documentElement.style.setProperty('--hoshino-particle-glow', toRgba(particleColor, 0.7));
    document.documentElement.style.setProperty('--hoshino-particle-speed', (settings.particleSpeed || 420) + 'ms');

    var mainCursor = createCursor('hoshino-cursor-main', mainSvg);
    var inputCursor = createCursor('hoshino-cursor-input', inputSvg);

    if (mainCursor) {
        document.body.appendChild(mainCursor);
    }
    if (inputCursor) {
        document.body.appendChild(inputCursor);
    }

    var position = { x: 0, y: 0 };
    var target = { x: 0, y: 0 };
    var visible = false;
    var activeCursor = null;
    var rafId = null;

    // 将数值限制在安全范围内。
    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    // 将十六进制颜色转换为 rgba，用于光晕。
    function toRgba(hex, alpha) {
        var value = hex.replace('#', '');
        if (value.length === 3) {
            value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2];
        }
        if (value.length !== 6) {
            return 'rgba(255, 95, 162, ' + alpha + ')';
        }
        var r = parseInt(value.substring(0, 2), 16);
        var g = parseInt(value.substring(2, 4), 16);
        var b = parseInt(value.substring(4, 6), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    // 创建包含内联 SVG 的光标容器。
    function createCursor(id, svg) {
        if (!svg) {
            return null;
        }
        var container = document.createElement('div');
        container.className = 'hoshino-cursor';
        container.id = id;
        container.innerHTML = svg;
        return container;
    }

    // 判断是否命中输入类元素。
    function isInputElement(targetEl) {
        if (!targetEl) {
            return false;
        }
        if (targetEl.closest) {
            var closest = targetEl.closest('input, textarea, [contenteditable="true"]');
            return !!closest;
        }
        return false;
    }

    // 根据目标元素与设置选择显示的光标。
    function updateCursorVisibility(element) {
        if (inputEnabled && isInputElement(element)) {
            setActiveCursor(inputCursor || mainCursor);
            return;
        }
        if (!inputEnabled && mainEnabled) {
            setActiveCursor(mainCursor);
            return;
        }
        if (mainEnabled) {
            setActiveCursor(mainCursor);
            return;
        }
        if (inputEnabled) {
            setActiveCursor(inputCursor);
        }
    }

    // 切换主光标与输入光标的可见性。
    function setActiveCursor(cursor) {
        activeCursor = cursor;
        [mainCursor, inputCursor].forEach(function (el) {
            if (!el) {
                return;
            }
            if (el === cursor) {
                if (visible) {
                    el.classList.add('is-visible');
                }
            } else {
                el.classList.remove('is-visible');
            }
        });
    }

    // 记录目标位置并启动渲染循环。
    function onMove(event) {
        target.x = event.clientX;
        target.y = event.clientY;
        visible = true;
        updateCursorVisibility(event.target);
        if (activeCursor) {
            activeCursor.classList.add('is-visible');
        }
        if (!rafId) {
            rafId = requestAnimationFrame(tick);
        }
    }

    // 平滑将光标移动到目标位置。
    function tick() {
        position.x += (target.x - position.x) * 0.35;
        position.y += (target.y - position.y) * 0.35;
        if (activeCursor) {
            activeCursor.style.transform = 'translate(' + position.x + 'px, ' + position.y + 'px)';
        }
        rafId = null;
        if (visible) {
            rafId = requestAnimationFrame(tick);
        }
    }

    // 鼠标离开窗口时隐藏光标。
    function onLeave() {
        visible = false;
        [mainCursor, inputCursor].forEach(function (el) {
            if (el) {
                el.classList.remove('is-visible');
            }
        });
    }

    // 点击时生成粒子爆裂效果。
    function onClick(event) {
        var count = clamp(settings.particleCount || 10, 4, 20);
        for (var i = 0; i < count; i += 1) {
            spawnParticle(event.clientX, event.clientY);
        }
    }

    // 创建单个粒子并随机方向与距离。
    function spawnParticle(x, y) {
        var particle = document.createElement('div');
        particle.className = 'hoshino-particle';
        var angle = Math.random() * Math.PI * 2;
        var distance = 18 + Math.random() * 18;
        var offsetX = Math.cos(angle) * distance;
        var offsetY = Math.sin(angle) * distance;
        particle.style.setProperty('--hoshino-particle-x', offsetX + 'px');
        particle.style.setProperty('--hoshino-particle-y', offsetY + 'px');
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        document.body.appendChild(particle);
        particle.addEventListener('animationend', function () {
            particle.remove();
        });
    }

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseleave', onLeave);
    document.addEventListener('click', onClick);
})();
