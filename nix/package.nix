{
  lib,
  demostf-api-php,
  dev ? false,
}: let
  inherit (lib.sources) sourceByRegex;
in
  demostf-api-php.buildComposerProject (finalAttrs: {
    pname = "demostf-api";
    version = "0.1.0";

    composerNoDev = !dev;

    src = sourceByRegex ../. ["composer.*" "(src|test)(/.*)?"];

    vendorHash =
      if dev
      then "sha256-PBp2PHoKfM66BjWxbEt5suKlkUxDxXdxhhCVzfRbJdo="
      else "sha256-EYWCR2aJAoyWvEX+SML4Fb3F3KGcUtwCgqhAGT6ZjZ4=";

    composerStrictValidation = false;

    doCheck = false;
  })
