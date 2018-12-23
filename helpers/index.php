<?php

function re_mkdir($path)
{
    // 如果目录存在返回 ture
    if (is_dir($path)) {
        return true;
    }
    // 如果上级目录存在 创建目录
    if (is_dir(dirname($path))) {
        return mkdir($path);
    }
    // 递归 查找父目录
    re_mkdir(dirname($path));
    return mkdir($path);
}
