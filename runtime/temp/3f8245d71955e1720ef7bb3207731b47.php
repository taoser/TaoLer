<?php /*a:1:{s:39:"E:\github\TaoLer\addons\qq\view\qq.html";i:1730427734;}*/ ?>
<script>    var qq = `
    <div class="footer-sns"><a class="sns-wx" href="javascript:;" aria-label="icon"><i class="layui-icon layui-icon-login-wechat sns-icon"></i><span style="background-image:url(<?php echo htmlentities((string) $qq['wxqr']); ?>);"></span></a><a class="sns-wx" href="javascript:;" aria-label="icon"><i class="layui-icon layui-icon-chat sns-icon"></i><span style="background-image:url(<?php echo htmlentities((string) $qq['qqqr']); ?>);"></span></a><a href="<?php echo htmlentities((string) $qq['qq']); ?>" target="_blank" rel="nofollow noopener" aria-label="icon" title="点击跟我聊天"><i class="layui-icon layui-icon-login-qq sns-icon"></i></a></div>`;

    layui.jquery(".footer-col.footer-col-sns").append(qq);
</script>