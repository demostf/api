{
  stdenv,
  rustPlatform,
  fetchFromGitHub,
  lib,
}: let
  inherit (lib.sources) sourceByRegex;
in
  rustPlatform.buildRustPackage rec {
    pname = "demostf-parser";
    version = "0.1.0";

    src = fetchFromGitHub {
      owner = "demostf";
      repo = "parser";
      rev = "v0.5.1";
      hash = "sha256-H6ypYeZRxaMP/qRZoO2bp7OzmePMNUaBbcswsa0b9Hs=";
    };

    cargoLock = {
      lockFile = ./parser-Cargo.lock;
      outputHashes = {
        "schemars-0.8.16" = "sha256-mQR56Ym76gSRulZrThmZHHw2JfhEgYhWXabwaYmyMYs=";
      };
    };
  }