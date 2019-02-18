<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 为文章/页面显示浏览量
 *
 * @package SkyViews
 * @author yiqiang
 * @version 1.0.0
 * @link http://blog.yiqiang.online
 */
class SkyViews_Plugin implements Typecho_Plugin_Interface
{
    public static $count=0;
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $info = self::SqlInstall();
        Typecho_Plugin::factory('Widget_Archive')->singleHandle = array('SkyViews_Plugin', 'insertAndCount');
        Typecho_Plugin::factory('Widget_Archive')->___views = array('SkyViews_Plugin', 'views');
        return _t($info);
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {

    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @param null $archive
     * @return void
     */
    public static function views($archive = null)
    {
        if ($archive) {
            $db = self::getDb();
            $query = $db->select("count(*) as count")->from("table.views")->where("cid = $archive->cid");
            $result = $db->fetchObject($query);
            self::$count = $result->count;
        }
        echo "<li>浏览: " . self::$count . "</li>";
    }

    /**
     * 创建表
     * @Created by PhpStorm.
     * @Author: yiqiang0932@gmail.com
     * @DateTime: 2019/2/12 13:21
     */
    public static function SqlInstall()
    {
        try {
            $db = self::getDb();
            $prefix = $db->getPrefix();
            $db->query("create table ".$prefix."views (
                                `id` INT NOT NULL AUTO_INCREMENT ,
                                `cid` INT NOT NULL ,
                                `ip` VARCHAR(20) NOT NULL ,
                                `created_at` timestamp NOT NULL ,
                                PRIMARY KEY (`id`),
                                KEY ix_cid(`cid`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8",Typecho_Db::WRITE);
            return '创建浏览量数据表，插件启用成功';
        } catch (Typecho_Db_Exception $e) {
        }

    }

    public static function getDb()
    {
        try {
            return Typecho_Db::get();
        } catch (Typecho_Db_Exception $e) {
        }
    }

    /**
     * 插入一条数据并返回总数
     * @param $archive
     * @return mixed
     * @throws Typecho_Db_Exception
     * @Created by PhpStorm.
     * @Author: yiqiang0932@gmail.com
     * @DateTime: 2019/2/12 13:22
     */
    public static function insertAndCount($archive)
    {
        $db = self::getDb();
        $ip = (new Typecho_Request())->getIp();
        $exist_ip = $db->fetchRow($db->select("ip")
            ->from("table.views")
            ->where("cid = $archive->cid and ip = '$ip'")
        );
        if (!$exist_ip){
            $insert = $db->insert("table.views")->rows([
                'cid'=> $archive->cid,
                'ip'=> $ip
            ]);
            $db->query($insert);
        }
        $query = $db->select("count(*) as count")->from("table.views")->where("cid = $archive->cid");
        $result = $db->fetchObject($query);
        self::$count = $result->count;
    }
}
