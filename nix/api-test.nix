{
  rustPlatform,
  fetchgit,
  pkg-config,
  openssl,
}:
rustPlatform.buildRustPackage {
  pname = "demostf-api-test";
  version = "0.1.4";

  src = fetchgit {
    url = "https://github.com/demostf/api-test";
    rev = "b2a8446e9b12c84d2c9228e4babe5d34132d3298";
    hash = "sha256-Zn6P4ukhoxqP+16ZkLBbqzM9DsTLmSNa4zrkhmyzy/I";
    fetchLFS = true;
  };

  buildInputs = [openssl];

  nativeBuildInputs = [pkg-config];

  doCheck = false;

  cargoHash = "sha256-Irv6atngsh0hPJ256tMxer3nR0PjBcaOJLVldnPnwUs=";
  meta.mainProgram = "api-test";
}
