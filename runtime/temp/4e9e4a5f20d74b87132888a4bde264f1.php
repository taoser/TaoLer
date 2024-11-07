<?php /*a:2:{s:39:"E:\github\TaoLer\addons\qq\view\qq.html";i:1730427734;s:49:"E:\github\TaoLer\addons\leftlayer\view\index.html";i:1729822030;}*/ ?>
<!--
 * @Author: TaoLer <317927823@qq.com> * @Date: 2022-06-30 11:39:59
 * @LastEditTime: 2022-07-02 10:14:26
 * @LastEditors: TaoLer
 * @Description: 左悬浮HTMl
 * @FilePath: \TaoLer\addons\leftLayer\view\index.html
 * Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
--><div class="statistics layui-hide-xs"><div class="border-bottom text-center text-blod statistics-item"><div class="f-13">资源总数</div><div class="statistics_text"><?php echo htmlentities((string) $leftlayer['total']); ?>+</div></div><div class="border-bottom text-center text-blod statistics-item"><div class="f-13">今日更新</div><div class="statistics_text"><?php echo htmlentities((string) $leftlayer['todaynum']); ?></div></div><div class="border-bottom text-center text-blod statistics-item"><div class="f-13">会员总数</div><div class="statistics_text"><?php echo htmlentities((string) $leftlayer['usernum']); ?></div></div><div class="border-bottom text-center text-blod statistics-item"><div class="f-13">今日注册</div><div class="statistics_text"><?php echo htmlentities((string) $leftlayer['todayusernum']); ?></div></div><div class="text-center text-blod statistics-item"><img src="/static/addons/leftlayer/img/vipp.png" alt="" class="statistics-item-img"></div></div><script>layui.link('/static/addons/leftlayer/css/leftlayer.css')</script>