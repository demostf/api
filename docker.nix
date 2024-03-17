{
  demostf-api,
  demostf-parser,
  php,
  runtimeShell,
  writeScriptBin,
  fakeNss,
  coreutils,
  bash,
  dockerTools
}: let
  phpWithExtensions = php.buildEnv {
    extensions = ({ enabled, all }: enabled ++ (with all; [pdo apcu]));
    extraConfig = ''
      post_max_size = 150M
      upload_max_filesize = 150M
    '';
  };
in dockerTools.buildLayeredImage {
    name = "demostf/api";
    tag = "latest";
    maxLayers = 10;

    contents = [
      demostf-api
      demostf-parser
      phpWithExtensions
      dockerTools.caCertificates
      coreutils
      bash
      fakeNss
      (writeScriptBin "start-server" ''
        #!${runtimeShell}
        php-fpm -F -y ${./php-fpm.conf}
      '')
    ];

    extraCommands = ''
      mkdir -p tmp
      chmod 1777 tmp
      ln -s ${demostf-api}/share/php/demostf-api app
    '';

    config = {
      Cmd = [ "start-server" ];
      Env = [
        "PARSER_PATH=${demostf-parser}/bin/parse_demo"
      ];
      ExposedPorts = {
        "9000/tcp" = {};
      };
    };
  }