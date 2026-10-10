#!/bin/sh

set -eu

: "${MINIO_ROOT_USER:?}"
: "${MINIO_ROOT_PASSWORD:?}"
: "${MINIO_ACCESS_KEY:?}"
: "${MINIO_SECRET_KEY:?}"
: "${MINIO_BUCKET:?}"

if [ "$MINIO_ACCESS_KEY" = "$MINIO_ROOT_USER" ]; then
    echo "STOP : le compte applicatif doit être différent du compte root."
    exit 1
fi

if [ "${#MINIO_SECRET_KEY}" -lt 12 ]; then
    echo "STOP : secret applicatif trop court."
    exit 1
fi

case "$MINIO_BUCKET" in
    ''|*[!a-z0-9.-]*)
        echo "STOP : nom de bucket invalide."
        exit 1
        ;;
esac

if [ "${#MINIO_BUCKET}" -lt 3 ] || [ "${#MINIO_BUCKET}" -gt 63 ]; then
    echo "STOP : le nom du bucket doit contenir entre 3 et 63 caractères."
    exit 1
fi

echo "Attente de MinIO..."

attempt=0

until mc alias set storage http://minio:9000 \
    "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD" >/dev/null 2>&1 \
    && mc ready storage >/dev/null 2>&1
do
    attempt=$((attempt + 1))

    if [ "$attempt" -ge 30 ]; then
        echo "STOP : MinIO indisponible."
        exit 1
    fi

    sleep 2
done

echo "Création du bucket..."
mc mb --ignore-existing "storage/$MINIO_BUCKET"

cat > /tmp/insite-policy.json <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:ListBucket",
        "s3:GetBucketLocation"
      ],
      "Resource": [
        "arn:aws:s3:::${MINIO_BUCKET}"
      ]
    },
    {
      "Effect": "Allow",
      "Action": [
        "s3:GetObject",
        "s3:PutObject",
        "s3:DeleteObject",
        "s3:AbortMultipartUpload",
        "s3:ListMultipartUploadParts"
      ],
      "Resource": [
        "arn:aws:s3:::${MINIO_BUCKET}/blog/*",
        "arn:aws:s3:::${MINIO_BUCKET}/games/*",
        "arn:aws:s3:::${MINIO_BUCKET}/about/*",
        "arn:aws:s3:::${MINIO_BUCKET}/announcements/*"
      ]
    }
  ]
}
POLICY

echo "Configuration de la politique..."
mc admin policy create \
    storage insite-app-policy /tmp/insite-policy.json

echo "Création du compte applicatif..."
mc admin user add \
    storage "$MINIO_ACCESS_KEY" "$MINIO_SECRET_KEY"

mc admin policy attach \
    storage insite-app-policy --user "$MINIO_ACCESS_KEY"

echo "Vérification des permissions applicatives..."

mc alias set application http://minio:9000 \
    "$MINIO_ACCESS_KEY" "$MINIO_SECRET_KEY" >/dev/null

echo "insite-storage-check" > /tmp/storage-check.txt

test_key="about/profiles/.provision-check-$(date +%s)-$$"

mc cp /tmp/storage-check.txt \
    "application/$MINIO_BUCKET/$test_key"

mc cat "application/$MINIO_BUCKET/$test_key" \
    | grep -qx 'insite-storage-check'

mc rm "application/$MINIO_BUCKET/$test_key"

echo "PASS : bucket initialisé"
echo "PASS : compte applicatif configuré"
echo "PASS : lecture, écriture et suppression validées"
