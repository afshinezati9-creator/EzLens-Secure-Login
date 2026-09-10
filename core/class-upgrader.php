<?php
class EzLens_Auth_Upgrader {
    const DB_VERSION='5.4.0';
    
    public static function maybe_upgrade(){
        if(version_compare((string)get_option('ezlens_auth_db_version','0'),self::DB_VERSION,'<')) {
            self::install();
            update_option('ezlens_auth_db_version',self::DB_VERSION);
            EzLens_Auth_Settings::clear_cache();
            
            // ===== Commerce Table Upgrade =====
            $commerce_install = EZLAUTH_MODULES_DIR . 'commerce/class-install.php';
            if (file_exists($commerce_install)) {
                require_once $commerce_install;
                if (class_exists('EzLens_Commerce_Install')) {
                    $current_version = EzLens_Commerce_Install::get_schema_version();
                    if (version_compare($current_version, EzLens_Commerce_Install::DB_VERSION, '<')) {
                        EzLens_Commerce_Install::install();
                    }
                }
            }
        }
    }
    
    public static function install(){
        foreach(EzLens_Auth_Settings::get_defaults() as $k=>$v){
            if(get_option('ezlens_auth_'.$k,false)===false) add_option('ezlens_auth_'.$k,$v,false);
        }
        self::repair_legacy_id_defaults();
        self::campaign_delivery_table();
        self::campaign_unsubscribe_table();
        self::audit_table();
        self::notifications_table();
        self::app_tokens_table();
        self::email_log_table();
        self::campaign_recipients_table();
        self::campaign_indexes();
        self::migrate_legacy_sms();
        
        // ===== Commerce Table Install =====
        $commerce_install = EZLAUTH_MODULES_DIR . 'commerce/class-install.php';
        if (file_exists($commerce_install)) {
            require_once $commerce_install;
            if (class_exists('EzLens_Commerce_Install')) {
                EzLens_Commerce_Install::install();
            }
        }
    }

    private static function migrate_legacy_sms(){
        $map=['sms_api_key'=>'otp_sms_api_key','sms_line_number'=>'otp_sms_line','sms_template_id'=>'otp_sms_template','sms_enabled'=>'otp_sms_enabled'];
        foreach($map as $old=>$new){$v=get_option('ezlens_auth_'.$old,false);if($v!==false && $v!=='' && get_option('ezlens_auth_'.$new,false)==='')update_option('ezlens_auth_'.$new,$v,false);}
    }

    private static function campaign_delivery_table(){global $wpdb;$table=$wpdb->prefix.'ezlens_campaign_delivery';$charset=$wpdb->get_charset_collate();$sql="CREATE TABLE {$table} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,campaign_id bigint(20) unsigned NOT NULL,recipient_email varchar(190) DEFAULT '',recipient_phone varchar(30) DEFAULT '',recipient_name varchar(190) DEFAULT '',channel varchar(20) NOT NULL DEFAULT 'sms',provider varchar(50) DEFAULT '',status varchar(20) NOT NULL DEFAULT 'failed',response longtext,message_id varchar(190) DEFAULT '',retry_count smallint(5) unsigned NOT NULL DEFAULT 0,created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,sent_at datetime DEFAULT NULL,PRIMARY KEY(id),KEY campaign_id(campaign_id),KEY status(status),KEY provider(provider),KEY created_at(created_at)) {$charset};";require_once ABSPATH.'wp-admin/includes/upgrade.php';dbDelta($sql);}
    
    private static function campaign_recipients_table(){
        global $wpdb;
        $t=$wpdb->prefix.'ezlens_campaign_recipients';
        $c=$wpdb->get_charset_collate();
        self::simple_table($t,"CREATE TABLE {$t} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) unsigned NOT NULL,
            recipient_key char(64) NOT NULL,
            source varchar(20) NOT NULL DEFAULT 'custom',
            source_id bigint(20) unsigned NOT NULL DEFAULT 0,
            name varchar(190) DEFAULT '',
            email varchar(190) DEFAULT '',
            phone varchar(30) DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'pending',
            response longtext,
            message_id varchar(190) DEFAULT '',
            retry_count smallint(5) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            sent_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE KEY campaign_recipient(campaign_id,recipient_key),
            KEY campaign_status(campaign_id,status),
            KEY campaign_email(campaign_id,email),
            KEY campaign_phone(campaign_id,phone),
            KEY status(status),
            KEY updated_at(updated_at)
        ) {$c};");
    }
    
    private static function email_log_table(){global $wpdb;$t=$wpdb->prefix.'ezlens_email_log';$c=$wpdb->get_charset_collate();self::simple_table($t,"CREATE TABLE {$t} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,user_id bigint(20) unsigned NOT NULL DEFAULT 0,campaign_id bigint(20) unsigned NOT NULL DEFAULT 0,to_email varchar(190) NOT NULL,subject varchar(255) DEFAULT '',status varchar(20) NOT NULL DEFAULT 'failed',error_message text,latency_ms decimal(10,2) DEFAULT 0,created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),KEY user_id(user_id),KEY campaign_id(campaign_id),KEY status(status),KEY created_at(created_at)) {$c};");}

    /**
     * Add indexes only when they do not already exist.
     * Older versions used unconditional ALTER TABLE statements, which caused
     * repeated database errors on every request.
     */
    private static function campaign_indexes(){
        global $wpdb;
        $tables = [
            $wpdb->prefix.'ezlens_campaign_contacts' => [
                'phone' => 'ADD KEY phone (phone)',
                'created_at' => 'ADD KEY created_at (created_at)',
            ],
            $wpdb->prefix.'ezlens_campaign_group_relations' => [
                'campaign_group' => 'ADD KEY campaign_group (campaign_id,group_id)',
            ],
            $wpdb->prefix.'ezlens_campaign_groups' => [
                'type' => 'ADD KEY type (type)',
            ],
        ];

        foreach ($tables as $table => $indexes) {
            if (!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table))) {
                continue;
            }
            foreach ($indexes as $index_name => $alter) {
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SHOW INDEX FROM {$table} WHERE Key_name = %s",
                    $index_name
                ));
                if (!$exists) {
                    $wpdb->query("ALTER TABLE {$table} {$alter}");
                }
            }
        }
    }

    /**
     * Repair legacy invalid defaults left by old installations.
     * MySQL/MariaDB must never receive DEFAULT '' on numeric AUTO_INCREMENT ids.
     */
    private static function repair_legacy_id_defaults(){
        global $wpdb;
        $tables = [
            $wpdb->prefix.'ezlens_app_tokens',
            $wpdb->prefix.'ezlens_campaign_unsubscribes',
            $wpdb->prefix.'ezlens_notifications',
            $wpdb->prefix.'ezlens_audit_log',
            $wpdb->prefix.'ezlens_email_log',
        ];
        foreach ($tables as $table) {
            if (!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table))) {
                continue;
            }
            $column = $wpdb->get_row("SHOW COLUMNS FROM {$table} LIKE 'id'");
            if (!$column) {
                continue;
            }
            $type = strtolower((string)$column->Type);
            $extra = strtoupper((string)$column->Extra);
            if (strpos($type, 'int') === false) {
                continue;
            }
            // Rebuild the id definition only when it is not already correct.
            if (strpos($extra, 'AUTO_INCREMENT') === false || (string)$column->Default !== '') {
                $wpdb->query("ALTER TABLE {$table} MODIFY COLUMN id bigint(20) unsigned NOT NULL AUTO_INCREMENT");
            }
        }
    }
    
    private static function simple_table($name,$sql){require_once ABSPATH.'wp-admin/includes/upgrade.php';dbDelta($sql);}
    
    private static function campaign_unsubscribe_table(){global $wpdb;$t=$wpdb->prefix.'ezlens_campaign_unsubscribes';$c=$wpdb->get_charset_collate();self::simple_table($t,"CREATE TABLE {$t} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,channel varchar(20) NOT NULL,email varchar(190) DEFAULT '',phone varchar(30) DEFAULT '',token_hash char(64) DEFAULT '',created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),KEY channel(channel),KEY email(email),KEY phone(phone),KEY token_hash(token_hash)) {$c};");}
    
    private static function audit_table(){global $wpdb;$t=$wpdb->prefix.'ezlens_audit_log';$c=$wpdb->get_charset_collate();self::simple_table($t,"CREATE TABLE {$t} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,user_id bigint(20) unsigned NOT NULL DEFAULT 0,action varchar(100) NOT NULL,object_type varchar(50) DEFAULT '',object_id bigint(20) unsigned NOT NULL DEFAULT 0,details longtext,ip varchar(45) DEFAULT '',user_agent text,created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),KEY user_id(user_id),KEY action(action),KEY created_at(created_at),KEY object(object_type,object_id)) {$c};");}
    
    private static function notifications_table(){global $wpdb;$t=$wpdb->prefix.'ezlens_notifications';$c=$wpdb->get_charset_collate();self::simple_table($t,"CREATE TABLE {$t} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,user_id bigint(20) unsigned NOT NULL,title varchar(255) NOT NULL,message text NOT NULL,type varchar(40) DEFAULT 'info',url varchar(500) DEFAULT '',is_read tinyint(1) NOT NULL DEFAULT 0,created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),KEY user_id(user_id),KEY is_read(is_read),KEY created_at(created_at)) {$c};");}
    
    private static function app_tokens_table(){global $wpdb;$t=$wpdb->prefix.'ezlens_app_tokens';$c=$wpdb->get_charset_collate();self::simple_table($t,"CREATE TABLE {$t} (id bigint(20) unsigned NOT NULL AUTO_INCREMENT,user_id bigint(20) unsigned NOT NULL,token_hash char(64) NOT NULL,device_name varchar(190) DEFAULT '',platform varchar(40) DEFAULT '',expires_at datetime NOT NULL,created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,last_used_at datetime DEFAULT NULL,revoked_at datetime DEFAULT NULL,PRIMARY KEY(id),UNIQUE KEY token_hash(token_hash),KEY user_id(user_id),KEY expires_at(expires_at)) {$c};");}
}