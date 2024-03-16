{
  inputs = {
    nixpkgs.url = "nixpkgs/release-23.11";
    utils.url = "github:numtide/flake-utils";
  };

  outputs = {
    self,
    nixpkgs,
    utils,
  }:
    utils.lib.eachDefaultSystem (system: let
      overlays = [
        (import ./overlay.nix)
      ];
      pkgs = import nixpkgs {
        inherit system overlays;
      };
      npmLd = pkgs.writeShellScriptBin "npm" ''
        PATH="$PATH ${pkgs.nodejs_20}/bin" LD=$CC ${pkgs.nodejs_20}/bin/npm $@
      '';
      nodeLd = pkgs.writeShellScriptBin "node" ''
        LD=$CC ${pkgs.nodejs_20}/bin/node $@
      '';
    in rec {
      packages = rec {
        inherit (pkgs) demostf-api demostf-api-docker demostf-parser;
        docker = demostf-api-docker;
        default = demostf-api;
      };
      devShells.default = pkgs.mkShell {
        nativeBuildInputs = with pkgs; [
          gnumake
          php
          npmLd
          nodeLd
        ];
      };
    });
}
