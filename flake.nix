{
  inputs = {
    nixpkgs.url = "nixpkgs/release-23.11";
    utils.url = "github:numtide/flake-utils";
    flocken = {
      url = "github:mirkolenz/flocken/v2";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs = {
    self,
    nixpkgs,
    utils,
    flocken,
  }:
    utils.lib.eachDefaultSystem (system: let
      inherit (builtins) getEnv;
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
      inherit (flocken.legacyPackages.${system}) mkDockerManifest;
    in rec {
      packages = rec {
        inherit (pkgs) demostf-api demostf-api-docker demostf-parser;
        docker = demostf-api-docker;
        default = demostf-api;

        dockerManifest = mkDockerManifest {
          tags = ["latest"];
          registries = {
            "docker.io" = {
              enable = true;
              repo = "demostf/api";
              username = "$DOCKERHUB_USERNAME";
              password = "$DOCKERHUB_TOKEN";
            };
          };
          version = "1.0.0";
          images = with self.packages; [x86_64-linux.demostf-api-docker aarch64-linux.demostf-api-docker];
        };
      };
      devShells.default = pkgs.mkShell {
        nativeBuildInputs = with pkgs; [
          gnumake
          php
          phpPackages.composer
          npmLd
          nodeLd
        ];
      };
    });
}
