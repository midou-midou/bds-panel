<?php
    require_once 'lib/User.php';
    require_once 'lib/Util.php';
    require_once 'lib/Minecraft.php';

    $user = User::auth();
    
    $new_ports = Minecraft::get_new_server_ports();

    $server_add = isset($_POST['server_add']) ? true : false;
    $server_id = $_POST['server_id'] ?? null;
    $server_name = $_POST['server_name'] ?? null;
    $server_port = $_POST['server_port'] ?? $new_ports['server_port'];
    $server_port_ipv6 = $_POST['server_port_ipv6'] ?? $new_ports['server_port_ipv6'];
    $server_seed = $_POST['server_seed'] ?? null;

    if ($server_add) {
        $server = new Server();
        $server->server_id = $server_id;
        $server->server_name = $server_name;
        $server->server_port = $server_port;
        $server->server_port_ipv6 = $server_port_ipv6;
        $server->server_seed = $server_seed;

        try {
            if (Minecraft::add_server($server)) {
                add_session_message('服务器已添加');
                header('Location: ./');
                exit;
            } else {
                $error_message = '添加服务器失败';
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
?>
<?php include_once 'header.php'; ?>

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>添加服务器</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="./">服务器列表</a></li>
                    <li class="active">添加服务器</li>
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
                            <?php echo nl2br(h($error_message)) ?>
                        </div>
                    <?php } ?>
                    <form action="server-add.php" method="post">
                        <div class="form-group">
                            <label for="server_id" class="control-label mb-1">服务器ID Server ID</label>
                            <input id="server_id" name="server_id" type="text" class="form-control" value="<?php echo h($server_id); ?>" placeholder="请输入字母(小写)" required>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_name" class="control-label mb-1">服务器名Server Name</label>
                            <input id="server_name" name="server_name" type="text" class="form-control server_name" value="<?php echo h($server_name); ?>" placeholder="请输入字母or拼音" required>
                        </div>
                        <div class="form-group has-success">
                            <label for="server_port" class="control-label mb-1">端口号Port</label>
                            <br>
                            IPv4<input id="server_port" name="server_port" type="text" class="form-control server_port" value="<?php echo h($server_port); ?>" placeholder="IPv4" required>
                            <br>
                            IPv6<input id="server_port_ipv6" name="server_port_ipv6" type="text" class="form-control server_port_ipv6" value="<?php echo h($server_port_ipv6); ?>" placeholder="IPv6" required>
                        </div>
                        <div class="form-group">
                            <label for="server_seed" class="control-label mb-1">种子seed</label>
                            <input id="server_seed" name="server_seed" type="text" class="form-control server_seed " value="<?php echo h($server_seed); ?>" placeholder="如果未指定,则随机生成">
                        </div>
                        <div>
                            <button type="submit" name="server_add" class="btn btn-lg btn-info btn-block">
                                <i class="fa fa-bolt fa-lg"></i>&nbsp;
                                <span>添加Add Server</span>
                            </button>
                        </div>
                    </form>

                </div> <!-- .card -->
            </div>

        </div>
    </div><!-- .animated -->
</div><!-- .content -->

<?php include_once 'footer.php'; ?>
