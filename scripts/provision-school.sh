#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  School Provisioning Script${NC}"
echo -e "${GREEN}======================================${NC}"

# Check arguments
if [ $# -lt 2 ]; then
    echo -e "${RED}Usage: $0 <school_id> <domain> [db_host]${NC}"
    echo -e "Example: $0 1 bangrak mysql_master"
    echo -e "Example: $0 2 saraburi 192.168.1.100"
    exit 1
fi

SCHOOL_ID=$1
SCHOOL_DOMAIN=$2
DB_HOST=${3:-mysql_master}
SCHOOL_DB="school_${SCHOOL_ID}"
R2_BUCKET="school${SCHOOL_ID}-${SCHOOL_DOMAIN}"

echo -e "${YELLOW}Provisioning school: ${SCHOOL_DOMAIN}${NC}"
echo -e "  School ID: ${SCHOOL_ID}"
echo -e "  Database: ${SCHOOL_DB}"
echo -e "  R2 Bucket: ${R2_BUCKET}"
echo -e "  Database Host: ${DB_HOST}"
echo ""

# Load environment variables
if [ -f .env ]; then
    source <(grep -E '^[A-Z]' .env | sed 's/=/="/' | sed 's/$/"/')
fi

# Get MySQL password
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root_secret}

# Step 1: Create MySQL database
echo -e "${YELLOW}[1/5] Creating MySQL database...${NC}"
docker exec ${DB_HOST} mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e \
    "CREATE DATABASE IF NOT EXISTS \`${SCHOOL_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}  Database ${SCHOOL_DB} created successfully${NC}"
else
    echo -e "${RED}  Failed to create database${NC}"
    exit 1
fi

# Step 2: Check if school record exists in master database
echo -e "${YELLOW}[2/5] Checking school record...${NC}"
SCHOOL_EXISTS=$(docker exec ${DB_HOST} mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" schools -N -e \
    "SELECT COUNT(*) FROM schools WHERE id = ${SCHOOL_ID} OR domain = '${SCHOOL_DOMAIN}';")

if [ "$SCHOOL_EXISTS" -gt 0 ]; then
    echo -e "${YELLOW}  School record exists, updating...${NC}"
    docker exec ${DB_HOST} mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" schools -e \
        "UPDATE schools SET domain = '${SCHOOL_DOMAIN}', database_name = '${SCHOOL_DB}', r2_bucket = '${R2_BUCKET}' WHERE id = ${SCHOOL_ID} OR domain = '${SCHOOL_DOMAIN}';"
else
    echo -e "${YELLOW}  Creating new school record...${NC}"
    docker exec ${DB_HOST} mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" schools -e \
        "INSERT INTO schools (name, slug, code, domain, database_name, r2_bucket, is_active) 
         VALUES ('${SCHOOL_DOMAIN}', '${SCHOOL_DOMAIN}', 'SCH${SCHOOL_ID}', '${SCHOOL_DOMAIN}', '${SCHOOL_DB}', '${R2_BUCKET}', 1)
         ON DUPLICATE KEY UPDATE domain = '${SCHOOL_DOMAIN}', database_name = '${SCHOOL_DB}', r2_bucket = '${R2_BUCKET}';"
fi

echo -e "${GREEN}  School record updated successfully${NC}"

# Step 3: Run migrations for the school database
echo -e "${YELLOW}[3/5] Running migrations...${NC}"
docker exec school-upload-app php artisan migrate \
    --database="school_${SCHOOL_ID}" \
    --force

if [ $? -eq 0 ]; then
    echo -e "${GREEN}  Migrations completed successfully${NC}"
else
    echo -e "${RED}  Migrations failed${NC}"
    exit 1
fi

# Step 4: Create R2 bucket (requires Cloudflare credentials)
echo -e "${YELLOW}[4/5] Creating R2 bucket...${NC}"
if [ -n "$CF_ACCOUNT_ID" ] && [ -n "$CF_TOKEN" ]; then
    RESPONSE=$(curl -s -X POST "https://api.cloudflare.com/client/v4/accounts/${CF_ACCOUNT_ID}/r2/buckets" \
        -H "Authorization: Bearer ${CF_TOKEN}" \
        -H "Content-Type: application/json" \
        -d "{\"name\": \"${R2_BUCKET}\"}")

    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo -e "${GREEN}  R2 bucket ${R2_BUCKET} created successfully${NC}"
    else
        ERROR_MSG=$(echo "$RESPONSE" | grep -o '"message":"[^"]*"' | head -1)
        echo -e "${YELLOW}  R2 bucket creation: ${ERROR_MSG:-Bucket may already exist}${NC}"
    fi
else
    echo -e "${YELLOW}  Skipping R2 bucket creation (CF_ACCOUNT_ID or CF_TOKEN not set)${NC}"
fi

# Step 5: Summary
echo -e "${YELLOW}[5/5] Provisioning complete!${NC}"

echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  Provisioning Summary${NC}"
echo -e "${GREEN}======================================${NC}"
echo -e "  School Domain: ${SCHOOL_DOMAIN}"
echo -e "  Database: ${SCHOOL_DB}"
echo -e "  R2 Bucket: ${R2_BUCKET}"
echo -e "  URL: http://${SCHOOL_DOMAIN}.school.com"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Configure DNS CNAME: ${SCHOOL_DOMAIN} -> your-server-ip"
echo -e "  2. Set up SSL certificate for subdomain"
echo -e "  3. Update .env with APP_BASE_DOMAIN=${SCHOOL_DOMAIN}.school.com"
echo ""
