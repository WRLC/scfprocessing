# scfprocessing

Processing tools for SCF staff built on the Alma API

## Local Development

### Dependencies

* [Docker Desktop](https://www.docker.com/products/docker-desktop/)
* [WRLC/local-dev-traefik](https://github.com/WRLC/local-dev-traefik) reverse proxy (for local networking of Docker containers)
* Local SSH key for git functionality in the PHP container (`~/.ssh/id_rsa`)
* Local git configuration file for git functionality in the PHP container (`~/.gitconfig`)

### Getting Started

1. Clone the repository:
    ```bash
    git clone git@github.com:WRLC/scfprocessing.git
    ```
2. Start the Docker containers:
    ```bash
    cd scfprocessing
    docker-compose up -d
    ```
3. Populate the database container with a backup of the production database:
    ```
    mysql -u root -proot --port 3311 database < /path/to/mysql/backup.sql.gz
    ```
4. SSH into the `scf-processing` container:
    ```bash
    docker exec -i -t scf-processing /bin/bash
    ```
5. Copy local `.env` file from `.env.template`:
    ```bash
    cp .env.template .env
    ```
6. Replace the Alma API key values in the `.env` file with the following working keys from the Ex Libris Developer Network:
    ```bash
    CANNED_REPORTS=[CannedReports]
    SCF_REFILE=[SCF Refile]
    ```
7. Replace the `GOOGLE_SHEET` value in the `.env` file with the API key for the "Refiling - Mismatch Tray Barcode Tracking (Responses)" Google Sheet.
    ```bash
    GOOGLE_SHEET=[Actual API Key]
    ```
8. Update `SSH_KEY_FILE` and `GIT_CONFIG_FILE` in the `.env` file to match the paths of your local SSH key and git configuration file—if they don't match the values in the template:
    ```bash
    SSH_KEY_FILE=~/.ssh/id_rsa
    GITCONFIG=~/.gitconfig
    ```
9. Visit the application in your browser at [https://scf-processing.wrlc.localhost](https://scf-processing.wrlc.localhost)