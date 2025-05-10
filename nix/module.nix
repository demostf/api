{
  config,
  pkgs,
  lib,
  ...
}: let
  cfg = config.services.demostf.api;
  fpmCfg = config.services.phpfpm.pools.demostf-api;
  exporterCfg = config.services.prometheus.exporters.php-fpm;
  phpPackage = pkgs.php.buildEnv {
    extensions = {
      enabled,
      all,
    }:
      enabled ++ (with all; [pdo apcu]);
    extraConfig = ''
      post_max_size = 150M
      upload_max_filesize = 150M
    '';
  };
in {
  options = {
    services.demostf.api = with lib; {
      enable = mkEnableOption "autosleep";
      package = mkOption {
        type = types.package;
        defaultText = literalExpression "pkgs.demostf-api";
        description = "package to use";
      };
      baseDomain = mkOption {
        type = types.str;
        description = "demo host domain";
      };
      apiDomain = mkOption {
        type = types.str;
        default = "api.${cfg.baseDomain}";
        description = "api domain";
      };
      hostDomain = mkOption {
        type = types.str;
        default = "static.${cfg.baseDomain}";
        description = "demo host domain";
      };
      demoRoot = mkOption {
        type = types.str;
        description = "path the demos are stored";
      };
      keyFile = mkOption {
        type = types.str;
        description = "path containing key environment variables";
      };
    };
  };
  config = lib.mkIf cfg.enable {
    services.nginx.virtualHosts.${cfg.apiDomain} = {
      useACMEHost = cfg.baseDomain;
      forceSSL = true;

      extraConfig = ''
        try_files $uri /index.php?$query_string /index.php;
      '';
      locations = {
        "~ ^(.+?\\.php)(/.*)?$" = {
          extraConfig = ''
            fastcgi_param  PATH_INFO $2;
            fastcgi_pass   unix:${fpmCfg.socket};
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME ${cfg.package}/share/php/demostf-api/src/public/index.php;
            include ${pkgs.nginx}/conf/fastcgi_params;
            client_max_body_size 250m;
          '';
        };
        "= /upload" = {
          extraConfig = ''
            fastcgi_pass   unix:${fpmCfg.socket};
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME ${cfg.package}/share/php/demostf-api/src/public/upload.php;
            include ${pkgs.nginx}/conf/fastcgi_params;
            client_max_body_size 250m;
          '';
        };
        "/metrics" = {
          proxyPass = with exporterCfg; "http://${listenAddress}:${toString port}";
        };
      };
    };

    services.phpfpm.pools.demostf-api = {
      inherit phpPackage;
      settings = {
        "clear_env" = "no";
        "pm" = "dynamic";
        "pm.max_children" = "25";
        "pm.start_servers" = "5";
        "pm.min_spare_servers" = "5";
        "pm.max_spare_servers" = "15";
        "pm.status_path" = "/fpm-status";
        "catch_workers_output" = "yes";
        "listen.owner" = "nginx";
        "listen.group" = "nginx";
      };
      phpEnv = {
        BASE_HOST = cfg.baseDomain;
        DEMO_ROOT = cfg.demoRoot;
        DEMO_HOST = cfg.hostDomain;
        DB_TYPE = "pgsql";
        DB_HOST = "/run/postgresql";
        DB_PORT = "5432";
        DB_DATABASE = "demostf";
        DB_USERNAME = "demostf";
        APP_ROOT = "https://${cfg.apiDomain}";
        PARSER_PATH = "${pkgs.demostf-parser}/bin/parse_demo";
      };
      user = "demostf";
      group = "demostf";
    };

    systemd.services.phpfpm-demostf-api.serviceConfig = {
      EnvironmentFile = cfg.keyFile;
    };

    services.prometheus.exporters.php-fpm = {
      enable = true;
      environmentFile = pkgs.writeText "php-fpm-exporter.env" ''
        PHP_FPM_SCRAPE_URI="unix://${fpmCfg.socket};/fpm-status"
      '';
      listenAddress = "127.0.0.1";
    };
    systemd.services.prometheus-php-fpm-exporter.serviceConfig = {
      SupplementaryGroups = ["nginx"];
      RestrictAddressFamilies = ["AF_UNIX"];
    };
  };
}
