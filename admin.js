jQuery(function ($) {
    // 初始化 WordPress 颜色选择器。
    $('.hoshino-color-field').wpColorPicker();

    // 打开 SVG 专用的媒体选择框。
    function openMediaFrame(targetField, previewField) {
        var frame = wp.media({
            title: '选择SVG',
            library: { type: 'image/svg+xml' },
            button: { text: '使用该SVG' },
            multiple: false
        });

        // 更新隐藏的附件 ID 与预览地址。
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + targetField).val(attachment.id);
            $('#' + previewField).val(attachment.url);
        });

        frame.open();
    }

    // 绑定上传按钮打开媒体选择框。
    $(document).on('click', '.hoshino-media-upload', function () {
        var target = $(this).data('target');
        var preview = $(this).data('preview');
        openMediaFrame(target, preview);
    });

    // 清空已选择的附件与预览地址。
    $(document).on('click', '.hoshino-media-clear', function () {
        var target = $(this).data('target');
        var preview = $(this).data('preview');
        $('#' + target).val('');
        $('#' + preview).val('');
    });
});
