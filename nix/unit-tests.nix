{
  pkgs,
  lib,
  ...
}: {
  name = "demostf-api-unit-tests";
  nodes.machine = {config, ...}: {
    config = {
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
    };
  };

  testScript = let
    php = lib.getExe pkgs.demostf-api-php;
    api = pkgs.demostf-api-dev;
    initSql = pkgs.fetchurl {
      url = "https://github.com/demostf/db/raw/refs/heads/master/schema.sql";
      hash = "sha256-tdMYDxlvpuQRxHglX46sCldxzsh1cDxkch2lGWnFH8E=";
    };
  in ''
    machine.succeed("mkdir /demos && chmod 0777 /demos");
    machine.wait_for_unit("postgresql")
    machine.succeed("sudo -u demostf psql demostf demostf < ${initSql}");
    machine.succeed("sudo -u postgres psql postgres postgres -c \"alter user demostf with password 'demostf';\"");
    machine.succeed("cd ${api}/share/php/demostf-api; DB_HOST='localhost'\
        DB_TYPE='pgsql'\
        DB_PORT='5432'\
        DB_USERNAME='demostf'\
        DB_PASSWORD='demostf'\
        DB_DATABASE='demostf'\
        BASE_URL='http://localhost/'\
        EDIT_KEY='edit'\
        ${php} ./vendor/bin/phpunit test", timeout=180)
  '';
}
