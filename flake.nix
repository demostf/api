{
  inputs = {
    nixpkgs.url = "nixpkgs/nixos-24.11";
    flakelight = {
      url = "github:nix-community/flakelight";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };
  outputs = {flakelight, ...}:
    flakelight ./. {
      pname = "demostf-api";
      withOverlays = [(import ./nix/overlay.nix)];
      packages = {
        api = pkgs: pkgs.demostf-api;
        api-dev = pkgs: pkgs.demostf-api-dev;
        api-test = pkgs: pkgs.demostf-api-test;
      };
      checks = {
        integration-tests = pkgs: pkgs.nixosTest (import ./nix/integration-tests.nix);
        unit-tests = pkgs: pkgs.nixosTest (import ./nix/unit-tests.nix);
      };
      formatters = pkgs: {
        "*.nix" = pkgs.lib.getExe pkgs.alejandra;
      };
      devShell.packages = pkgs: [
        pkgs.demostf-api-php.packages.composer
        pkgs.demostf-api-php
        pkgs.nodejs
      ];
      nixosModules = {outputs, ...}: {
        default = {
          pkgs,
          config,
          lib,
          ...
        }: {
          imports = [./nix/module.nix];
          config = lib.mkIf config.services.demostf.api.enable {
            nixpkgs.overlays = [outputs.overlays.default];
            services.demostf.api.package = lib.mkDefault pkgs.demostf-api;
          };
        };
      };
    };
}
