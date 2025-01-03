final: prev: {
  demostf-parser = final.callPackage ./parser.nix {};
  demostf-api = final.callPackage ./package.nix {};
  demostf-api-dev = final.callPackage ./package.nix {dev = true;};
  demostf-api-test = final.callPackage ./api-test.nix {};
  demostf-api-php = final.php83.buildEnv {
    extraConfig = "memory_limit = 2G";
    extensions = {
      enabled,
      all,
    }:
      enabled
      ++ (with all; [
        pdo
        apcu
      ]);
  };
}
