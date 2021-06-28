<?php

class Minecraft {
    private static $screen_user = 'mcpe';
    private static $server_base_dir = '/home/mcpe';
    private static $servers_json = __DIR__ . '/../servers.json';

    /**
     * サーバー情報取得
     * 获取所有服务器信息
     */
    public static function get_servers($options = []) {
        $servers = @json_decode(file_get_contents(self::$servers_json));
        if (!$servers) {
            $servers = [];
        }
        if (count($servers) > 0) {
            usort($servers, function($a, $b){
                if ($a->server_port == $b->server_port) {
                    return $a->server_name < $b->server_name ? -1 : 1;
                }
                return $a->server_port < $b->server_port ? -1 : 1;
            });
            //exec(var_dump($servers[0]->server_id));
            if (!isset($options['status']) || $options['status'] != '0') {
                $statuses = self::get_server_statuses($servers[0]->server_id);
                foreach ($servers as $k => $v) {
                    if ($statuses[$v->server_id] ?? null) {
                        $servers[$k] = (object) array_merge((array) $v, (array) $statuses[$v->server_id]);
                    }
                }
            }

            foreach ($servers as $k => $v) {
                $servers[$k]->is_active = $v->is_active ?? null;
            }
        }
        return $servers;
    }

    /**
     * サーバー情報一件取得
     * 获取服务器信息
     */
    public static function get_server($server_id, $options = []) {
        $servers = self::get_servers($options);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server_id) {
                return $v;
            }
        }
        return false;
    }

    /**
     * サーバー追加
     * 添加服务器
     */
    public static function add_server($server) {
        self::check_server($server, true);

        $servers = self::get_servers([ 'status' => 0 ]);
        $servers[] = $server;
        $ret = file_put_contents(self::$servers_json, json_encode($servers));

        // ファイルのコピー
        // 建立服务器PS直接复制无法执行，先舍去
        //exec('sudo -u ' . self::$screen_user . ' cp -Rp ' . self::$server_base_dir . '/server-bedrock-template ' . self::$server_base_dir . '/server-bedrock-' . $server->server_id, $outputs, $retval);
       
        // server.propertiesを更新
        // 更新server.properties
        self::update_server_properties($server->server_id, 'level-name', $server->server_id);
        self::update_server_properties($server->server_id, 'server-name', $server->server_name);
        self::update_server_properties($server->server_id, 'server-port', $server->server_port);
        self::update_server_properties($server->server_id, 'server-portv6', $server->server_port_ipv6);
        if ($server->server_seed) {
            self::update_server_properties($server->server_id, 'level-seed', $server->server_seed);
        }

        return $ret;
    }

    /**
     * サーバー更新
     * 更新服务器
     */
    public static function update_server($server) {
        self::check_server($server);

        $servers = self::get_servers([ 'status' => 0 ]);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server->server_id) {
                $servers[$k] = (object) array_merge((array) $v, (array) $server);
            }
        }
        $ret = file_put_contents(self::$servers_json, json_encode($servers));

        // server.propertiesを更新
        // 更新server.properties
        self::update_server_properties($server->server_id, 'server-name', $server->server_name);
        self::update_server_properties($server->server_id, 'server-port', $server->server_port);
        self::update_server_properties($server->server_id, 'server-portv6', $server->server_port_ipv6);

        return $ret;
    }

    /**
     * サーバー削除
     * 删除服务器
     */
    public static function delete_server($server_id) {
        $servers = self::get_servers([ 'status' => 0 ]);
        foreach ($servers as $k => $v) {
            if ($v->server_id == $server_id) {
                unset($servers[$k]);
            }
        }
        $ret = file_put_contents(self::$servers_json, json_encode($servers));
        
        // ファイルの削除
        // 删除备份PS直接复制无法执行，先舍去
        //exec('sudo -u ' . self::$screen_user . ' rm -rf ' . self::$server_base_dir . '/server-bedrock-' . $server_id, $outputs, $retval);

        return $ret;
    }

    /**
     * サーバー情報チェック
     * 检查服务器信息
     */
    public static function check_server($server, $is_new = false) {
        $errors = [];
        if ($server->server_id && preg_match('/[^a-z]/', $server->server_id)) {
            $errors[] = '仅使用字母输入服务器ID';
        }
        if ($is_new) {
            // 新規作成
            // 添加新服务器时检查信息
            if (!$server->server_id) {
                $errors[] = '请输入您的服务器ID';
            } else if (file_exists(self::$server_base_dir . '/server-bedrock-' . $server->server_id)) {
                $errors[] = '此服务器ID已存在';
            }
        }
        if (!$server->server_name) {
            $errors[] = '请输入服务器名称';
        }
        if (count($errors) > 0) {
            throw new Exception("输入内容不正确\n" . implode("\n", $errors));
        }
    }
    
    /**
     * サーバーステータスを取得
     * 获取服务器状态
     */
    public static function get_server_statuses($server_ID) {
        exec('sudo -u ' . self::$screen_user . ' screen -ls', $outputs, $retval);
        $server_statuses = [];
        if ($retval == 0 && count($outputs) > 0) {
            foreach ($outputs as $line) {
                if (preg_match('/\s+([^\s]+)server-bedrock-([^\s]+).*Detached.*/', $line, $matches)) {
                    $server_statuses[$server_ID] = (object) [
                        'is_active' => true
                    ];
                }
            }
        }
        return $server_statuses;
    }

    /**
     * サーバーを起動
     * 启动服务器
     */
    public static function start_server($server_id) {
        // 最大起動数をチェック
        // 最大启动服务器的数量
        $server_status = 0;
        $server = null;
        $servers = self::get_servers();
        $active_servers = [];
        foreach ($servers as $v) {
            if ($v->is_active) {
                $active_servers[$v->server_id] = $v;
            }
            if ($server_id == $v->server_id) {
                $server = $v;
            }
        }
        if (count($active_servers) >= 4) {
            throw new Exception("同时启动的服务器仅限4个以内");
        }
        if (!$server) {
            throw new Exception("找不到这样的服务器");
        }
        foreach ($active_servers as $v) {
            if ($v->server_port == $server->server_port) {
                throw new Exception("相同的端口号（IPv4）已被使用。");
            } else if ($v->server_port_ipv6 == $server->server_port_ipv6) {
                throw new Exception("相同的端口号（IPv6）已被使用。");
            }
        }

        //exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/server-bedrock-' . $server_id . '/start.sh', $outputs, $retval);
        exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/server-bedrock-template/start.sh', $outputs, $retval);
        sleep(1);
        if ($retval == 0) {
            return true;
        }
        throw new Exception("启动服务器失败:" . implode(' ', $outputs));
    }

    /**
     * サーバーを停止
     * 停止服务器
     */
    public static function stop_server($server_id) {
        //exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/server-bedrock-' . $server_id . '/stop.sh', $outputs, $retval);
        exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/server-bedrock-template/stop.sh', $outputs, $retval);        
        sleep(1);
        if ($retval == 0 || count($outputs) == 0) {
            return true;
        }
        throw new Exception("停止服务器失败:" . implode("\n", $outputs));
    }

    /**
     * サーバーの設定ファイルを更新
     * 更新服务器配置文件
     */
    public static function update_server_properties($server_id, $key, $value) {
        //exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/update-server-properties.sh server-bedrock-' . $server_id . ' ' . $key . ' ' . $value, $outputs, $retval);
        exec('sudo -u ' . self::$screen_user . ' ' . self::$server_base_dir . '/update-server-properties.sh server-bedrock-template' . $key . ' ' . $value, $outputs, $retval);

        if ($retval == 0) {
            return true;
        }
        throw new Exception("更新服务器配置文件失败:" . implode("\n", $outputs));
    }

    public static function get_new_server_ports() {
        $servers = self::get_servers([ 'status' => 0 ]);
        $new_ports = [
            'server_port' => 19130,
            'server_port_ipv6' => 19131,
        ];
        foreach ($servers as $v) {
            $new_ports['server_port'] = max($new_ports['server_port'], $v->server_port);
            $new_ports['server_port_ipv6'] = max($new_ports['server_port_ipv6'], $v->server_port_ipv6);
        }
        $new_ports['server_port'] += 2;
        $new_ports['server_port_ipv6'] += 2;
        return $new_ports;
    }
}

class Server {
    public $server_id;
    public $server_name;
    public $server_seed;
    public $server_port;
    public $server_port_ipv6;
    public $is_active;
}
?>