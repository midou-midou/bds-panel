<?php
    require_once 'lib/User.php';
    require_once 'lib/Util.php';
    require_once 'lib/Minecraft.php';

    $user = User::auth();
    
    $server_id = $_POST['server_id'] ?? null;
    if ($_GET['server_id'] ?? null) {
        $server_id = $_GET['server_id'];
    }

    $server = Minecraft::get_server($server_id);
    if (!$server) {
        header('Location: ./');
        exit;
    }

    $server_delete = isset($_POST['server_delete']) ? true : false;

    if ($server_delete) {
        if (Minecraft::delete_server($server_id)) {
            add_session_message('服务器删除成功');
            header('Location: ./');
            exit;
        } else {
            $error_message = '服务器删除失败';
        }
    }
?>
<?php include_once 'header.php'; ?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>确认服务器删除</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="./">服务器列表</a></li>
                    <li class="active">确认服务器删除</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">服务器信息</strong>
                </div>
                <div class="card-body">
                    <?php if ($error_message ?? null) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message ?>
                        </div>
                    <?php } ?>
                    <form action="server-delete.php" method="post">
                        <input type="hidden" name="server_id" value="<?php echo h($server->server_id); ?>">
                        <div class="form-group">
                            <label for="server_id" class="control-label mb-1">服务器ID Server Name</label>
                            <span><?php echo h($server->server_id); ?></span>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_name" class="control-label mb-1">服务器名Server Name</label>
                            <span><?php echo h($server->server_name); ?></span>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_port" class="control-label mb-1">端口号Port</label>
                            <br>
                            <span>IPv4<?php echo h($server->server_port); ?></span>
                            <br>
                            <span>IPv6<?php echo h($server->server_port_ipv6); ?></span>
                        </div>
                        <div>
                            <button type="submit" name="server_delete" class="btn btn-lg btn-danger btn-block" onclick="return confirm('删除后，将无法还原\n真的要删除它吗?会消失很久(梗~)');">
                                <i class="fa fa-o-trash fa-lg"></i>&nbsp;
                                <span>删除服务器delete server</span>
                            </button>
                        </div>
                    </form>

                </div> <!-- .card -->
            </div>

        </div>
    </div><!-- .animated -->
</div><!-- .content -->

<?php include_once 'footer.php'; ?>
