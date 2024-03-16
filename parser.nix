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
      rev = "v0.4.0";
      hash = "sha256-/1D5bNnJWRwuycaLidSyuYC36IHSnyA33HvcoUH4GpI=";
    };

    cargoSha256 = "sha256-UNslB5yKcVbuRELxbKQ1Bl2jHKoC3i0OEYq0UzTi1aU=";
  }