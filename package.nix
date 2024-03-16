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

    vendorHash = "sha256-ympq8XIdABkdjshYX7hJIO6XfFdYm0RA9s3f/n7om3I=";

    composerStrictValidation = false;

    doCheck = false;
  })