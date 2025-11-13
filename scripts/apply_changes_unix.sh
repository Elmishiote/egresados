#!/usr/bin/env bash
# Usage: ./scripts/apply_changes_unix.sh [HOST_BASE_URL]
# Example: ./scripts/apply_changes_unix.sh http://localhost/seybt
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

REMOTE_BRANCH="feature/adm-cuestionarios-student-access"
LOCAL_BRANCH="feature/adm-cuestionarios-student-access"

echo "1) Fetching remote refs..."
git fetch origin --prune

# If remote branch exists, create/sync local tracking branch; otherwise create local from main
if git ls-remote --exit-code --heads origin "$REMOTE_BRANCH" >/dev/null 2>&1; then
  echo "Remote branch $REMOTE_BRANCH found. Creating/switching to local tracking branch..."
  if git rev-parse --verify "$LOCAL_BRANCH" >/dev/null 2>&1; then
    git checkout "$LOCAL_BRANCH"
    git pull origin "$REMOTE_BRANCH"
  else
    git checkout -b "$LOCAL_BRANCH" --track "origin/$REMOTE_BRANCH"
  fi
else
  echo "Remote branch $REMOTE_BRANCH not found. Creating local branch from main..."
  git checkout main
  git pull origin main
  git checkout -b "$LOCAL_BRANCH"
fi

echo "2) Files updated in your local copy (pull applied)."

# Execute PHP script to create test users (CLI)
MAT1="amy"
PASS1="amy123"
MAT2="student"
PASS2="pass"
MAT3="S2025001"
PASS3="S2025001"
NOMBRE3="Alumno Prueba"
ESTATUS3="TerminoOctavo"

PHP_BIN="$(command -v php || true)"
if [ -z "$PHP_BIN" ]; then
  echo "ERROR: php not found in PATH. Install PHP or run the script via browser."
  exit 1
fi

SCRIPT_PATH="bttescha/auth/php/inserEstudiante.php"
if [ ! -f "$SCRIPT_PATH" ]; then
  echo "ERROR: $SCRIPT_PATH does not exist. Make sure the repo is present and the path is correct."
  exit 1
fi

echo "3) Creating test accounts using inserEstudiante.php..."
echo " -> Creating $MAT1 / $PASS1"
$PHP_BIN "$SCRIPT_PATH" "$MAT1" "$MAT1" "$PASS1" "Amy" "TerminoOctavo" || echo "Notice: creation $MAT1 failed or already exists."

echo " -> Creating $MAT2 / $PASS2"
$PHP_BIN "$SCRIPT_PATH" "$MAT2" "$MAT2" "$PASS2" "Student" "TerminoOctavo" || echo "Notice: creation $MAT2 failed or already exists."

echo " -> Creating $MAT3 / $PASS3 (test matricula)"
$PHP_BIN "$SCRIPT_PATH" "$MAT3" "$MAT3" "$PASS3" "$NOMBRE3" "$ESTATUS3" || echo "Notice: creation $MAT3 failed or already exists."

echo 

echo "Done. Now test in incognito mode:"
echo " - Open: /bttescha/AdmCuestionarios/index.php"
echo " - Enter matricula: $MAT3 (or $MAT1 / $MAT2) and select questionnaire 1 (or appropriate)."
echo 
