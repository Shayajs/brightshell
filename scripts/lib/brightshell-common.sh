#!/usr/bin/env bash
# Bibliothèque partagée pour les scripts Brightshell (Docker Compose + artisan).
# shellcheck disable=SC2034
_BR_LIB_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export BRIGHTSHELL_ROOT="$(cd "$_BR_LIB_DIR/../.." && pwd)"

brightshell_php_service_for_file() {
  local f="$1"
  local base
  base=$(basename "$f")
  if [[ "$base" == *shaya* ]] || [[ "$base" == *-dev.yaml ]] || [[ "$base" == *-dev.yml ]]; then
    echo "brightshell_dev_php"
  else
    echo "brightshell_app"
  fi
}

brightshell_db_service_for_file() {
  local f="$1"
  local base
  base=$(basename "$f")
  if [[ "$base" == *shaya* ]] || [[ "$base" == *-dev.yaml ]] || [[ "$base" == *-dev.yml ]]; then
    echo "brightshell_dev_db"
  else
    echo "brightshell_db"
  fi
}

# Fichier compose à utiliser pour artisan/composer (explicite, actif, ou vide = hôte).
brightshell_compose_file_for_ops() {
  cd "$BRIGHTSHELL_ROOT" || return 1
  if [[ -n "${BRIGHTSHELL_COMPOSE_FILE:-}" ]]; then
    echo "$BRIGHTSHELL_COMPOSE_FILE"
    return 0
  fi
  local f svc
  for f in docker-compose-shaya.dev.yaml docker-compose.yml; do
    [[ -f "$f" ]] || continue
    svc="$(brightshell_php_service_for_file "$f")"
    if docker compose -f "$f" ps -q "$svc" 2>/dev/null | grep -q .; then
      echo "$f"
      return 0
    fi
  done
  echo ""
}

brightshell_php_service_resolved() {
  local cf="$1"
  echo "${BRIGHTSHELL_PHP_SERVICE:-$(brightshell_php_service_for_file "$cf")}"
}

brightshell_docker_exec_php() {
  local cf="$1"
  shift
  local svc
  svc="$(brightshell_php_service_resolved "$cf")"
  docker compose -f "$cf" exec -T "$svc" "$@"
}

brightshell_artisan() {
  cd "$BRIGHTSHELL_ROOT" || exit 1
  local cf
  cf="$(brightshell_compose_file_for_ops)"
  if [[ -n "$cf" ]]; then
    brightshell_docker_exec_php "$cf" php artisan "$@"
  else
    php artisan "$@"
  fi
}

brightshell_composer() {
  cd "$BRIGHTSHELL_ROOT" || exit 1
  local cf
  cf="$(brightshell_compose_file_for_ops)"
  if [[ -n "$cf" ]]; then
    brightshell_docker_exec_php "$cf" composer "$@"
  else
    composer "$@"
  fi
}

brightshell_db_password() {
  local pass="brightshell_secret"
  local line
  if [[ -f "$BRIGHTSHELL_ROOT/.env" ]]; then
    line=$(grep -E '^DB_PASSWORD=' "$BRIGHTSHELL_ROOT/.env" | tail -1 || true)
    if [[ -n "$line" ]]; then
      pass="${line#DB_PASSWORD=}"
      pass="${pass%$'\r'}"
      pass="${pass#\"}"
      pass="${pass%\"}"
    fi
  fi
  echo "$pass"
}

brightshell_wait_db() {
  local cf="$1"
  local db_svc="${BRIGHTSHELL_DB_SERVICE:-$(brightshell_db_service_for_file "$cf")}"
  local pass
  pass="$(brightshell_db_password)"
  local i=0
  echo "Attente de MariaDB ($db_svc)…"
  while [[ $i -lt 60 ]]; do
    if docker compose -f "$cf" exec -T "$db_svc" mariadb-admin ping -h localhost -uroot -p"$pass" --silent 2>/dev/null; then
      echo "MariaDB prête."
      return 0
    fi
    sleep 2
    i=$((i + 1))
  done
  echo "Échec : MariaDB ne répond pas après ~120 s." >&2
  return 1
}

brightshell_ensure_networks_for_compose() {
  local cf="${1:-docker-compose.yml}"
  local base
  base=$(basename "$cf")
  if [[ "$base" == *shaya* ]] || [[ "$base" == *-dev.yaml ]] || [[ "$base" == *-dev.yml ]]; then
    docker network inspect dev_gateway_net >/dev/null 2>&1 || docker network create dev_gateway_net
  elif [[ "$base" == docker-compose.yml ]] || [[ "$cf" == */docker-compose.yml ]]; then
    docker network inspect www_laravel_net >/dev/null 2>&1 || docker network create www_laravel_net
  fi
}
