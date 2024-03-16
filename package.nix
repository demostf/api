{
  stdenv,
  php,
  lib,
}: let
  inherit (lib.sources) sourceByRegex;
  phpWithExtensions = php.withExtensions ({ enabled, all }: enabled ++ (with all; [pdo apcu]));
in
  phpWithExtensions.buildComposerProject (finalAttrs: {
    pname = "demostf-api";
    version = "0.1.0";

    src = sourceByRegex ./. ["composer.*" "(src|test)(/.*)?"];

    vendorHash = "sha256-EYWCR2aJAoyWvEX+SML4Fb3F3KGcUtwCgqhAGT6ZjZ4=";

    composerStrictValidation = false;

    doCheck = false;
  })