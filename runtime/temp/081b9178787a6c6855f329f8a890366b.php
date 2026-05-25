<?php /*a:2:{s:55:"E:\github\TaoLer\app\index\view\taoler\index\index.html";i:1779627432;s:43:"E:\github\TaoLer\addons\sign\view\sign.html";i:1779676713;}*/ ?>
<script>	layui.cache.user = {uid: '<?php echo isset($user['id']) ? htmlentities((string) $user['id']) : -1; ?>'};
	const signStatusUrlAddons = "<?php echo addons_url('sign://index/status'); ?>";
	const signInUrlAddons = "<?php echo addons_url('sign://index/sign'); ?>";
	const signRuleUrlAddons = "<?php echo addons_url('sign://index/getsignrule'); ?>";
	const signJsonUrlAddons = "<?php echo addons_url('sign://index/signJson'); ?>";
	const signRuleAddons ="<?php echo addons_url('sign://sign/signRule'); ?>";
	
	var signHtml = `<div class="fly-panel fly-signin" id="signin"><div class="fly-panel-title"><?php echo lang('Sign in'); ?><i class="fly-mid"></i><a href="javascript:;" class="fly-link" id="TAO_signinHelp"><?php echo lang('statement'); ?></a><i class="fly-mid"></i><a href="javascript:;" class="fly-link" id="TAO_signinTop"><?php echo lang('trends'); ?><span class="layui-badge-dot"></span></a><span class="fly-signin-days"></span></div><div class="fly-panel-main fly-signin-main"><?php if(session('?user_id')): ?><i class="layui-icon fly-loading layui-icon-loading"></i><?php else: ?><button class="layui-btn layui-btn-danger" id="TAO_signin"><?php echo lang('今日签到'); ?></button><?php endif; ?></div></div>`;

layui.$(".layui-col-md4").append(signHtml);
</script><script src="/static/addons/sign/js/sign.js" type="text/javascript" charset="utf-8"></script>