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
      rev = "2449c9666019a99b791f84d3c0c38b6b7c71ad20";
      hash = "sha256-V0rm9pVfZUGPrD3raOJ7O6EQkbxVG6cIquWvdFkGPgM=";
    };

    cargoBuildFlags = ''
      --bin parse_demo
    '';

    doCheck = false;

    cargoLock = {
      lockFile = ./parser-Cargo.lock;
      outputHashes = {
        "schemars-0.8.16" = "sha256-mQR56Ym76gSRulZrThmZHHw2JfhEgYhWXabwaYmyMYs=";
      };
    };
  }