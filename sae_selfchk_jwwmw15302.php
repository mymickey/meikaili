<?php
/*
*************************************************************
*   this is a script for check services by  user self
*   You can delete it, or keep it .
*   Each service self-examination, will auto generate it
*************************************************************
*
*   Sina App Engine                 http://sae.sina.com.cn/
*
*********************************************** pangee ******
*/

define( 'my_version' , '1.0' );

//precheck
if( v('check_status') && v('callback') ){
    $back = array(
                    'status'=>'im online!' , 
                    'version'=>my_version
                    );
    $end_num = end( explode('_',v('callback')) );
    echo 'Request.JSONP.request_map.request_'. intval( $end_num ) .'('.json_encode( $back ).')';
    exit();
}

//sign
$md5key = substr( md5( SAE_ACCESSKEY.SAE_SECRETKEY ) , 8 , 16 );
if( $md5key != v('md5key') )
    die( 'access deny!' );


@session_start();

//<!-- main -->
$route = array( 'mysql' , 'kvdb' , 'mail' , 'storage' );
if( !($type=v('type')) || !in_array($type,$route) )
    err( -21 , 'param error!' , 1 );
//<!-- /main -->

if( !$_GET['ac'] ):
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>服务自查 @ SAE</title>
    <link rel="stylesheet" type="text/css" href="http://sae.sina.com.cn/static/css/base.css?v=2011112401" />
    <link rel="stylesheet" type="text/css" href="http://sae.sina.com.cn/static/css/common.css?v=2011112401" /> 
    <script type="text/javascript" src="http://sae.sina.com.cn/static/script/mootools.1.3.1.min.js" ></script> 
    <style type="text/css">
    .yui-g{padding:0;}
    </style>
    <style type="text/css">
        html{background:#fff;}
        body{background:#fff;width:350px;overflow:hidden;}
        .mse{background:#fff;margin-bottom:-35px; }
        .mse .onerow{clear:both;padding-top:8px;}
        .mse .onerow h3{font-size:12px;color:#33f;}
        .mse .onerow .param{}
        .msg .onerow .param p{ margin:0; }
        .mse .onerow .param input.text{ font-size:14px;padding:2px 3px;color:#888;margin:3px 0;width:350px; }
        .mse .onerow .param input.button{ font-size:14px;padding:4px 10px; }
        .mse .onerow .param span.notice{ padding-left:15px;font-size:14px;color:#0C3; }
        .mse .onerow .param span.error{ color:#c00; }
        .mse hr{ border:0;border-bottom:1px dashed #ccc;padding-top:9px;clear:both;margin-bottom:10px;margin-left:-5px;margin-right:-5px; }
        .mse small{font-size:12px;}
        .mse .mt5{margin-top:5px;}
    </style>
    <script language="javascript" type="text/javascript">
        var api = '<?=$_SERVER["SCRIPT_URI"]?>?type=<?=$type?>&md5key=<?=$md5key?>&';
        function error_rep( where , msg ){
            $(where).addClass( 'error' ).set('html',msg);
            setTimeout( function(){ $(where).set('html','').removeClass('error'); } , 2500 );
        }
        function err( msg ){
            error_rep( 'errnotice' , msg );
        }
        function notice_rep( where , msg ){
            $(where).set('html',msg);
            setTimeout( function(){ $(where).set('html',''); } , 2000 );
        }
        function urlencode(s){
            return encodeURIComponent( s );
        }
        function ajaxbox_retry(url , rep , msg){
            var req=new Request.JSON({
                method:'post',
                evalScripts: true,
                url: url,
                onComplete: function(resp){
                    backcode( resp , rep , msg );
                }
            }).send();
        }
    </script>
</head>
<body>
<?php
    if( $type=='mysql' ):
?>
    <script language="javascript" type="text/javascript">
        function mysql_insertinto( rep ){
            var key = $('insertinto_1').get('value');
            var value = $('insertinto_2').get('value');
            if( !key || !value || (key=="selfchk_id（VARCHAR）...") || (value=="selfchk_value（TEXT）...") )
            {
                return error_rep( rep , '请先填写必要信息' );
            }
            ajaxbox_retry( api+'ac=insertinto&key='+urlencode(key)+'&value='+urlencode(value)+'&r='+Math.random() , rep , '设置成功' );
        }
        function mysql_select( rep ){
            var key = $('select_1').get('value');
            if( !key || (key=="selfchk_id …") )
            {
                return error_rep( rep , '请先填写必要信息' );
            }
            ajaxbox_retry( api+'ac=select&key='+urlencode(key)+'&r='+Math.random() , rep , '获取成功' );
        }
        function mysql_truncate( rep ){
            ajaxbox_retry( api+'ac=truncate&r='+Math.random() , rep , '自检数据表已清空' );
        }
        function backcode( resp , rep , msg ){
            if( !resp.code ) return error_rep( rep , '网络传输失败，请重新尝试。' );
            var errmsg = '';
            if( resp.code<0 ){
                errmsg = resp.msg;
            }
            if( resp.code<0 && errmsg=='' ) errmsg='异常错误';
            if( errmsg!='' ){
                if( rep=='select_3' )
                    $('select_2').set('value','null');
                return error_rep( rep , errmsg );
            }
            if( rep=='select_3' ){
                $('select_2').set('value',resp.msg);
                notice_rep( rep , msg );
            }
            else
                notice_rep( rep , msg );
        }
    </script>
<div class="mse">
    <p>为了检测服务状态，将自动创建一个测试表`sae_selfchk_tb`，可用字段是`selfchk_id`,`selfchk_value`</p>
    <div class="onerow insert">
        <h3>INSERT INTO</h3>
        <div class="param">
            <p class="inputs">
                <input id="insertinto_1" class="text" type="text" value="selfchk_id（VARCHAR）..." maxlength="20" onfocus="if( $(this).get('value')=='selfchk_id（VARCHAR）...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','selfchk_id（VARCHAR）...').setStyle('color','#888')" />
                <input id="insertinto_2" class="text" type="text" value="selfchk_value（TEXT）..." maxlength="40" onfocus="if( $(this).get('value')=='selfchk_value（TEXT）...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','selfchk_value（TEXT）...').setStyle('color','#888')" />
            </p>
            <p class="buttons">
                <input type="button" class="button" onclick="mysql_insertinto('insertinto_3');" value=" 设置为以上数据 " />
                <span id="insertinto_3" class="notice"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <h3>SELECT</h3>
        <div class="param">
            <p class="inputs">
                <input id="select_1" class="text" type="text" value="selfchk_id …" maxlength="20" onfocus="if( $(this).get('value')=='selfchk_id …' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','selfchk_id …').setStyle('color','#888')" />
                <input id="select_2" class="text" type="text" value="selfchk_value …" readonly disabled="disabled"/>
            </p>
            <p class="buttons">
                <input type="button" class="button" onclick="mysql_select('select_3');" value=" 获取对应数据 " />
                <span class="notice" id="select_3"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <h3>TRUNCATE TABLE</h3>
        <div class="param mt5">
            <p class="buttons">
                <input type="button" class="button" onclick="mysql_truncate('truncate_1');" value=" 清空本服务的自检数据 " />
                <span class="notice" id="truncate_1"></span>
                <br/>
                <small>该操作将清空本服务自检生成的所有数据！</small>
            </p>
        </div>
    </div>
</div>
<?php
    elseif( $type=='kvdb' ):
?>
    <script language="javascript" type="text/javascript">
        function kv_set( rep ){
            var key = $('set_1').get('value');
            var value = $('set_2').get('value');
            if( !key || !value || (key=="key ...") || (value=="value ...") )
            {
                return error_rep( rep , '请先填写必要信息' );
            }
            ajaxbox_retry( api+'ac=set&key='+urlencode(key)+'&value='+urlencode(value)+'&r='+Math.random() , rep , '设置成功' );
        }
        function kv_get( rep ){
            var key = $('get_1').get('value');
            if( !key || (key=="key …") )
            {
                return error_rep( rep , '请先填写必要信息' );
            }
            ajaxbox_retry( api+'ac=get&key='+urlencode(key)+'&r='+Math.random() , rep , '获取成功' );
        }
        function kv_flush( rep ){
            ajaxbox_retry( api+'ac=flush&r='+Math.random() , rep , '自检数据表已清空' );
        }
        function backcode( resp , rep , msg ){
            if( !resp.code ) return error_rep( rep , '网络传输失败，请重新尝试。' );
            var errmsg = '';
            if( resp.code<0 ){
                errmsg = resp.msg;
            }
            if( resp.code<0 && errmsg=='' ) errmsg='异常错误';
            if( errmsg!='' ){
                if( rep=='get_3' )
                    $('get_2').set('value','null');
                return error_rep( rep , errmsg );
            }
            if( rep=='get_3' ){
                $('get_2').set('value',resp.msg);
                notice_rep( rep , msg );
            }
            else
                notice_rep( rep , msg );
        }
    </script>
<div class="mse">
    <div class="onerow insert">
        <h3>SET</h3>
        <div class="param">
            <p class="inputs">
                <input id="set_1" class="text" type="text" value="key ..." maxlength="20" onfocus="if( $(this).get('value')=='key ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','key ...').setStyle('color','#888')" />
                <input id="set_2" class="text" type="text" value="value ..." maxlength="40" onfocus="if( $(this).get('value')=='value ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','value ...').setStyle('color','#888')" />
            </p>
            <p class="buttons">
                <input type="button" class="button" onclick="kv_set('set_3');" value=" 设置为以上数据 " />
                <span id="set_3" class="notice"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <h3>GET</h3>
        <div class="param">
            <p class="inputs">
                <input id="get_1" class="text" type="text" value="key …" maxlength="20" onfocus="if( $(this).get('value')=='key …' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','key …').setStyle('color','#888')" />
                <input id="get_2" class="text" type="text" value="value …" readonly disabled="disabled"/>
            </p>
            <p class="buttons">
                <input type="button" class="button" onclick="kv_get('get_3');" value=" 获取对应数据 " />
                <span class="notice" id="get_3"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <h3>FLUSH</h3>
        <div class="param mt5">
            <p class="buttons">
                <input type="button" class="button" onclick="kv_flush('flush_1');" value=" 清空本次自检数据 " a/>
                <span class="notice" id="flush_1"></span>
                <br/>
                <small>该操作将清空本服务自检生成的数据！</small>
            </p>
        </div>
    </div>
</div>
<?php
    elseif( $type=='storage' ):
        $stInfo = json_decode( v('stInfo') , true );
?>
    <script language="javascript" type="text/javascript">
        function setdomain( th ){
            var v = $(th).get('value');
            $('domain').set('value',v);
        }
        function precheck( rep ){
            var v = $('domain').get('value');
            if( v=='请先创建Domain' || v=='' ){
                error_rep( rep , '请先选取或创建Domain' );
                return false;
            }
            return true;
        }
        function backcode( resp , rep , msg ){
            if( !resp.code ) return error_rep( rep , '网络传输失败，请重新尝试。' );
            var errmsg = '';
            if( resp.code<0 ){
                errmsg = resp.msg;
            }
            if( resp.code<0 && errmsg=='' ) errmsg='异常错误';
            else{
                $('showurl').set('html','');
                return notice_rep( rep , msg );
            }
            if( errmsg!='' ){
                return error_rep( rep , errmsg );
            }
        }
        function iframe_notice( type , msg , url , domain ){
            var rep = 'notice_me';
            if( type == 1 ){
                var html = '<p style="margin-top:15px;"><a href="'+url+'" target="_blank">'+msg+'</a> <a href="javascript:void(0)" onclick="deletefile( \''+domain+'\' , \''+msg+'\' )" style="color:#c00" ><b>删除</b></a></p>';
                $('showurl').set('html',html);
                return notice_rep( rep , '上传成功！' );
            }else{
                return error_rep( rep , msg );
            }
        }
        function deletefile( domain , filename ){
            return ajaxbox_retry( api+'&ac=delete&domain='+domain+'&filename='+filename , 'notice_me' , '删除成功' );
        }
    </script>
<div class="mse">
    <p><font style="font-size:14px;">上传一个文件至指定Domain下</font><br/>(不支持批量上传，单个文件请小于1M)</p>
    <div class="onerow insert">
        <div class="param">
            <p class="inputs">
                <strong style="color:#03F;">Domain：</strong>
                <select onchange="setdomain(this);" id="sto_domain">
                    <?php
                    if( is_array($stInfo) ):
                        foreach( $stInfo as $st ):
                    ?>
                        <option value="<?=$st?>"><?=$st?></option>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <option>请先创建Domain</option>
                    <?php
                    endif;
                    ?>
                </select>
                <span id="errnotice" class="hide"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <div class="param">
            <form action="<?=$_SERVER["SCRIPT_URI"]?>?type=storage&ac=write&do=write" method="post" target="upup" enctype="multipart/form-data" >
                <input type="hidden" value="<?=isset($stInfo[0])?$stInfo[0]:''?>" name="domain" id="domain" />
                <p>选择上传文件: <br/> <input style="width:180px;" value=" 选择上传文件 " type="file" name="f" class="file" style="padding:5px 10px;" /></p>
                <p><input value=" 上传文件 " onclick="return precheck('notice_me','上传成功');" name="up" type="submit" class="button" style="padding:2px 8px;" /> <span class="notice" style="padding-top:3px;" id="notice_me"><span></p>
            </form>
            <iframe src="about:blank" id="upup" name="upup" class="hide"></iframe>
        </div>
        <div style="height:55px;display:block;" class="param" id="showurl"></div>
    </div>
</div>  
<?php
    elseif( $type=='mail' ):
?>
<script language="javascript" type="text/javascript">
function sendmail( rep ){
    var email       = $('email').get('value');
    var semail      = $('semail').get('value');
    var semailpwd   = $('semailpwd').get('value');
    var semailp     = $('semailp').get('value');
    var smtp        = $('semailsmtp').get('value');
    if( !email || (email=="email …") || !semail || !semailpwd || !semailp || !smtp )
    {
        return error_rep( rep , '请先填写必要信息' );
    }
    ajaxbox_retry( api+'ac=send&email='+urlencode(email)+'&semail='+urlencode(semail)+'&smtp='+urlencode(smtp)+'&semailpwd='+urlencode(semailpwd)+'&semailp='+urlencode(semailp)+'&r='+Math.random() , rep , '发送成功' );
}

function backcode( resp , rep , msg ){
    if( !resp.code ) return error_rep( rep , '网络传输失败，请重新尝试。' );
    var errmsg = '';
    if( resp.code<0 ){
        errmsg = resp.msg;
    }
    if( resp.code<0 && errmsg=='' ) errmsg='异常错误';
    if( errmsg!='' ){
        return error_rep( rep , errmsg );
    }
    notice_rep( rep , msg );
}
</script>
<div class="mse">
    <p><font style="font-size:12px;">为了检测服务状态，将向您指定的邮箱发送一封测试邮件</font></p>
    <div class="onerow insert">
        <h3>发件邮箱地址：</h3>
        <div class="param">
            <p class="inputs">
                <input id="semail" class="text" type="text" value="email ..." maxlength="100" onfocus="if( $(this).get('value')=='email ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','email ...').setStyle('color','#888')" />
            </p>
        </div>
        <h3>发件邮箱密码：</h3>
        <div class="param">
            <p class="inputs">
                <input id="semailpwd" class="text" type="password" value="password ..." maxlength="100" onfocus="if( $(this).get('value')=='password ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','password ...').setStyle('color','#888')" />
            </p>
        </div>
        <h3>发件邮箱服务器（smtp）：</h3>
        <div class="param">
            <p class="inputs">
                <input id="semailsmtp" class="text" type="text" value="smtp ..." maxlength="100" onfocus="if( $(this).get('value')=='smtp ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','smtp ...').setStyle('color','#888')" />
            </p>
        </div>
        <h3>发件邮箱端口（port）：</h3>
        <div class="param">
            <p class="inputs">
                <input id="semailp" class="text" type="text" value="port ..." maxlength="100" onfocus="if( $(this).get('value')=='port ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','port ...').setStyle('color','#888')" />
            </p>
        </div>
        <h3>收件邮箱地址：</h3>
        <div class="param">
            <p class="inputs">
                <input id="email" class="text" type="text" value="email ..." maxlength="100" onfocus="if( $(this).get('value')=='email ...' ) $(this).set('value','').setStyle('color','#000')" onblur="if( $(this).get('value')=='' ) $(this).set('value','email ...').setStyle('color','#888')" />
            </p>
            <p class="buttons">
                <input type="button" class="button" onclick="sendmail('mailnotice');" value=" 发送邮件 " />
                <span id="mailnotice" class="notice"></span>
            </p>
        </div>
    </div>
    <hr/>
    <div class="onerow insert">
        <h3>邮件内容：</h3>
        <div class="param mt5" style="font-size:12px;">
            <p class="inputs">
            邮件标题：SAE 发出的测试邮件<br/>
            邮件内容：这是SAE在为您检测服务时发出的测试邮件。
            </p>
        </div>
    </div>
    <hr/>
    <p>
        请您注意查收邮件，如果无法收到邮件请将您的邮箱线上提交给我们: <a href="http://sae.sina.com.cn/?m=feedback" target="_blank">意见反馈</a>
    </p>
</div>

<?php   
    endif;
?>
</body>
</html>
<?php
    exit();
    //end html
endif;
?>







<?php
/*
*************************************************************
*   this is a script for check services by  user self
*   You can delete it, or keep it .
*   Each service self-examination, will auto generate it
*************************************************************
*
*   Sina App Engine                 http://sae.sina.com.cn/
*
*********************************************** pangee ******
*   Mark: error code from 20
*       : 3x - mysql
*       : 4x - kvdb
*       : 5x - mail
*       : 6x - storage
*       : 7x - {reserved}
*/

$se = new selfexam( $type );
$se->$type();

//<!-- class -->
class selfexam{
    private $type;
    private $handle;
    
    protected $mysql_table_name = 'sae_selfchk_tb';
    
    function __construct( $type ){
        $this->type = strtolower($type);
        switch( $this->type ){
            case 'mysql':
                $this->handle = new SaeMysql();
                break;
            case 'kvdb':
                $this->handle = new SaeKV();
                break;
            case 'mail':
                $this->handle = new SaeMail();
                break;
            case 'storage':
                $this->handle = new SaeStorage();
                break;
        }
        if( !$this->handle )
            err( -22 , 'init class fail.' , 1 );
    }
    
    //<!-- mail -->
    public function mail(){
        $email      = urldecode(v( 'email' ));
        $email_send = urldecode(v( 'semail'));
        $email_pwd  = urldecode(v( 'semailpwd'));
        $email_port = urldecode(v( 'semailp'));
        $smtp       = urldecode(v( 'smtp' ));
        if( !$email || !$email_send || !$email_pwd || !$email_port )
            return err( -51 , 'mail param error.' );
        
        $opt = array(
                                'from'          => $email_send ,
                                'to'            => $email ,
                                'smtp_host'     => $smtp ,
                                'smtp_port'     => $email_port ,
                                'smtp_username' => $email_send ,
                                'smtp_password' => $email_pwd ,
                                'subject'       => 'SAE 发出的测试邮件' ,
                                'content'       => '这是SAE在为您检测服务时发出的测试邮件。'
                        );
        
        $mail = $this->handle;
        $mail->setOpt( $opt );
        $ret = $mail->send();
        if( !$ret )
            return err( -52 , 'send email fail.'.$mail->errmsg() );
        else
            return err( 1 , 'KVDB flush ok!' );
    
    }
    
    //<!-- storage -->
    public function storage(){
        if( !v('domain') || v('domain')=='请先创建Domain' )
            return err( -61 , 'no Domain' );
        
        $domain = v('domain');
        $s = $this->handle;
        
        switch( strtolower(v('do')) ){
            case 'write':
                if( !$_FILES['f'] )
                    return $this->serr( -62 , 'FILE null' );
                elseif( !$_FILES['f']['size']>4*1024 )
                    return $this->serr( -63 , 'success!' );
                $contents = file_get_contents( $_FILES['f']['tmp_name'] );
                $filename = $_FILES['f']['name'];
                if( $s->write( $domain , $filename , $contents ) ){
                    $url = $s->getUrl( $domain , $filename );
                    return $this->serr( 1 , $filename , $url );
                }else{
                    return $this->serr( -64 , 'storage write fail' );
                }
                break;
            case 'delete':
                if( !$filename=v('filename') )
                    return err( -65 , 'filename not exist' );
            
                if( $s->delete($domain , $filename) ){
                    return err( 1 , 'delete success!' );
                }else{
                    return err( -66 , 'storage delete fail.' );
                }
                break;
            default:
                return err( -67 , 'param is null' );
        }
    }
    private function serr( $type=1 , $msg , $url=NULL ){
        if( !$url )
            echo '<script language="javascript" type="text/javascript">window.parent.iframe_notice(  '.$type.' , "'.$msg.'" );</script>';
        else
            echo '<script language="javascript" type="text/javascript">window.parent.iframe_notice(  '.$type.' , "'.$msg.'" , "'.$url.'" );</script>';
        exit();
    }
    
    //<!-- KVDB -->
    public function kvdb(){
        $params     = array(    'prefix'    => 'sae_selfchk_' ,
                                'method'    => v('ac'),
                                'key'       => v('key'),
                                'value'     => v('value'),
                            );
        $method     = $params['method'];
        $memkey     = $params['prefix'].'memery';
        $kv         = $this->handle;
        $ret        = $kv->init();
        if( !$ret )
            return err( -41 , 'kvdb init fail.' );
        
        switch( strtolower($method) ){
            case 'set':
                //remember key
                $memery = $kv->get( $memkey );
                $memery = $memery.'";"'.urlencode($params['key']);
                $kv->set( $memkey , $memery );
                $ret = $kv->set( $params['key'] , $params['value'] );
                if( !$ret )
                    return err( -42 , 'kvdb set fail.' );
                else
                    return err( 1 , 'kvdb set success!' );
                break;
            case 'get':
                $ret = $kv->get( $params['key'] );
                if( !$ret ) return err( -43 , '无此键值' );
                else        return err( 1 , $ret );
                break;
            case 'flush':
                $memery = $kv->get( $memkey );
                $memery = explode( '";"' , $memery );
                $ret = true;
                if( is_array($memery) && count($memery)>0 ){
                    foreach( $memery as $mem ){
                        if( !trim($mem) ) continue;
                        $r = $kv->delete( $mem );
                        if( !$r ) $ret = false;
                    }
                }
                $r = $kv->delete( $memkey );
                return err( 1 , 'KVDB flush ok!' );
                break;
        }
    
    }
    
    
    //<!-- MYSQL -->
    public function mysql(){
        $params     = array(    'table' =>'sae_selfchk_tb',
                                'key'   =>v('key'),
                                'value' =>v('value'),
                            );
        $sqltype    = v('ac');
        
        if( !$sqltype || !isset($params['table']) )
            return err( -31 , 'param error (sqltype or tablename)' );
        
        $action = 'mysql_'.$sqltype;
        $ret = $this->$action( $params );
        if( !$ret ) return err( -32 , 'something wrong?! action error' );
    }
    private function mysql_insertinto( $params ){
        if( !$params['key'] || !$params['value'] )
            return err( -33 , 'param error ( key or value ) ' );
            
        $m  = $this->handle;
        //creat before insert
        $sql    = "show tables;";
        $tables = $m->getData( $sql );
        $var_tb = 'Tables_in_app_'.SAE_APPNAME;
        if( !$tables ) $this->mysql_creat_table();
        elseif( is_array($tables) ){
            $tag = false;
            foreach( $tables as $tb ){
                if( $tb[$var_tb] == $this->mysql_table_name ){
                    $tag = true;
                    break;
                }
            }
            if( $tag===false ) $this->mysql_creat_table();
        }
        //check key exist
        $sql    = "select count(*) from `".$this->mysql_table_name."` where `selfchk_id`='".$m->escape($params['key'])."' limit 0,1";
        $exist  = $m->getVar( $sql );
        if( $exist===false ) 
            return err( -35 , $m->errmsg() );
        elseif( $exist===0 || $exist=='0' ){
            $sql = "insert into `".$this->mysql_table_name."` ( `selfchk_id` , `selfchk_value` ) ";
            $sql.= "VALUES ( '".$m->escape($params['key'])."' , '".$m->escape($params['value'])."' )";
        }
        else{
            $sql = "update `".$this->mysql_table_name."` set `selfchk_value`='".$m->escape($params['value'])."' ";
            $sql.= "where `selfchk_id`='".$m->escape($params['key'])."'";
        }
        //insert or update
        $ret = $m->runsql( $sql );
        if( !$ret ) return err( -36 , 'insert/update fail.' );
        else{
            $skey = 'mysql_'.$params['key'];
            $_SESSION[$skey] = $_SERVER['REQUEST_TIME'];
            $_SESSION['sql_keys'] .= ';|;'.urlencode($params['key']);
            return err( 1 , 'insert into success!' );
        }
    }
    private function mysql_select( $params ){
        if( !$params['key'] )
            return err( -33 , 'param error ( key or value ) ' );
            
        $m      = $this->handle;
        $sql    = "select `selfchk_value` from `".$this->mysql_table_name."` where `selfchk_id`='".$m->escape($params['key'])."' limit 0,1";
        $ret    = $m->getVar( $sql );
        if( !$ret ){
            $skey = 'mysql_'.$params['key'];
            if( !isset($_SESSION[$skey]) )
                return err( -36 , '无此键值');
            else
                return err( -37 , 'MySQL主从延迟，请联系SAE官方' );
        }
        else        return err( 1 , $ret );
    }
    private function mysql_truncate( $params ){
        $m      = $this->handle;
        $sql    = "TRUNCATE TABLE `".$this->mysql_table_name."`;";
        $ret    = $m->runsql( $sql );
        if( !$ret ) return err( -36 , 'TRUNCATE fail.' );
        else{
            $sqlkeys = $_SESSION['sql_keys'];
            $sqls    = explode( ';|;' , $sqlkeys );
            if( is_array($sqls) && count($sqls)>0 ){
                foreach( $sqls as $v ){
                    $key = 'mysql_'.urldecode($v);
                    unset( $_SESSION[$key] );
                }
            }
            return err( 1 , 'truncate table success!' );
        }
    }
    private function mysql_creat_table(){
        $sql = 'CREATE TABLE `app_'.SAE_APPNAME.'`.`sae_selfchk_tb` (
                    `selfchk_id` VARCHAR( 80 ) NOT NULL ,
                    `selfchk_value` TEXT NULL ,
                    PRIMARY KEY ( `selfchk_id` )
                ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;
                ';
        $ret = $this->handle->runsql( $sql );
        if( !$ret ) return err( -34 , 'creat table fail.' , 1 );
        else        return $ret;
    }
    //<!-- /MYSQL -->

    
    function __destruct(){
        //亲，养成良好习惯哦。
        if( $this->type=='mysql' )
            $this->handle->closeDB();
    }
}

//<!-- function -->
function err( $code , $msg , $stop=NULL ){
    echo json_encode( array('code'=>$code,'msg'=>$msg) );
    if( $stop ) exit();
    return true;
}
function v( $key ){
    return $_REQUEST[$key]?$_REQUEST[$key]:false;
}
?>
