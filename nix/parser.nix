{
  rustPlatform,
  fetchFromGitHub,
}:
rustPlatform.buildRustPackage {
  pname = "demostf-parser";
  version = "0.5.1";

  src = fetchFromGitHub {
    owner = "demostf";
    repo = "parser";
    rev = "0cd87a8a40e2a6af637d831b272c2758cebd2f9c";
    hash = "sha256-bKcc0hWTkdYUDMI/DjUh45abuBeQEvkn6TsuAz02H5Y=";
  };

  cargoBuildFlags = ''
    --bin parse_demo
  '';

  doCheck = false;

  cargoHash = "sha256-/Fnw6l2fznrBK780E4q1PKFOkT0eiL+dE+UuhFA+V9M=";
  meta.mainProgram = "parse_demo";
}
