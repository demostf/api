{
  pkgs,
  lib,
  ...
}: {
  name = "demostf-api-client-test";
  nodes.machine = {config, ...}: let
    fpmCfg = config.services.phpfpm.pools.demostf-api;
  in {
    config = {
      environment.systemPackages = [pkgs.demostf-api-test];

      users.groups.demostf = {};
      users.users.demostf = {
        group = "demostf";
        isSystemUser = true;
      };

      services.postgresql = {
        enable = true;
        ensureDatabases = ["demostf"];
        ensureUsers = [
          {
            name = "demostf";
            ensureDBOwnership = true;
          }
        ];
        initialScript = pkgs.writeText "init-sql-script" ''
          CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;
        '';
      };

      services.nginx = {
        enable = true;
        virtualHosts."localhost" = {
          root = "/var/empty";
          extraConfig = ''
            try_files $uri /index.php?$query_string /index.php;
          '';
          locations = {
            "~ ^(.+?\\.php)(/.*)?$" = {
              extraConfig = ''
                fastcgi_param  PATH_INFO $2;
                fastcgi_pass   unix:${fpmCfg.socket};
                fastcgi_index  index.php;
                fastcgi_param  SCRIPT_FILENAME ${pkgs.demostf-api}/share/php/demostf-api/src/public/index.php;
                include ${pkgs.nginx}/conf/fastcgi_params;
                client_max_body_size 250m;
              '';
            };
            "= /upload" = {
              extraConfig = ''
                fastcgi_pass   unix:${fpmCfg.socket};
                fastcgi_index  index.php;
                fastcgi_param  SCRIPT_FILENAME ${pkgs.demostf-api}/share/php/demostf-api/src/public/upload.php;
                include ${pkgs.nginx}/conf/fastcgi_params;
                client_max_body_size 250m;
              '';
            };
            "/static/" = {
              alias = "/demos/";
            };
          };
        };
      };

      services.phpfpm.pools.demostf-api = {
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
        settings = {
          "clear_env" = "no";
          "pm" = "dynamic";
          "pm.max_children" = "25";
          "pm.start_servers" = "5";
          "pm.min_spare_servers" = "5";
          "pm.max_spare_servers" = "15";
          "catch_workers_output" = "yes";
          "listen.owner" = "nginx";
          "listen.group" = "nginx";
        };
        phpEnv = {
          BASE_HOST = "demos.tf";
          DEMO_ROOT = "/demos";
          DEMO_HOST = "localhost";
          DB_TYPE = "pgsql";
          DB_HOST = "/run/postgresql";
          DB_PORT = "5432";
          DB_DATABASE = "demostf";
          DB_USERNAME = "demostf";
          APP_ROOT = "http://localhost";
          EDIT_SECRET = "edit";
          PARSER_PATH = lib.getExe pkgs.demostf-parser;
        };
        user = "demostf";
        group = "demostf";
      };
    };
  };

  testScript = let
    initSql = pkgs.fetchurl {
      url = "https://github.com/demostf/db/raw/refs/heads/master/schema.sql";
      hash = "sha256-AwXN9mh9CRk6HWdvyUR+YdBkpmExNIDOIeDMz6XqjEQ=";
    };
  in ''
    machine.succeed("mkdir /demos && chmod 0777 /demos");
    machine.wait_for_unit("postgresql")
    machine.succeed("sudo -u demostf psql demostf demostf < ${initSql}");
    machine.succeed("sudo -u postgres psql postgres postgres -c \"alter user demostf with password 'demostf';\"");
    machine.wait_for_unit("phpfpm-demostf-api")
    machine.wait_for_unit("nginx")
    machine.wait_until_succeeds("curl http://127.0.0.1", timeout=45)
    machine.succeed("DB_URL='postgres://demostf:demostf@localhost/demostf'\
        BASE_URL='http://localhost/'\
        EDIT_KEY='edit'\
        api-test", timeout=180)
  '';
}
