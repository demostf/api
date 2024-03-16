final: prev: {
  demostf-parser = final.callPackage ./parser.nix {};
  demostf-api = final.callPackage ./package.nix {};
  demostf-api-docker = final.callPackage ./docker.nix {};
}