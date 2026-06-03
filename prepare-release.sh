#!/bin/bash
# Script de preparação do release para venda no Mercado Livre
# Uso: bash prepare-release.sh

VERSION="1.0.0"
RELEASE_NAME="sistema-pdv-v${VERSION}"
RELEASE_ZIP="${RELEASE_NAME}.zip"

echo "================================================"
echo "  Sistema PDV - Preparando Release v${VERSION}"
echo "================================================"

# Verificar se está na pasta correta
if [ ! -f "artisan" ]; then
    echo "❌ Execute este script na pasta raiz do projeto Laravel!"
    exit 1
fi

# Criar pasta temporária para o release
TEMP_DIR="/tmp/${RELEASE_NAME}"
echo "📁 Criando pasta temporária: ${TEMP_DIR}"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Copiar arquivos do projeto
echo "📋 Copiando arquivos do projeto..."
rsync -a --progress \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/app/public/produtos' \
    --exclude='${RELEASE_NAME}*' \
    . "$TEMP_DIR/"

# Copiar .env.example como .env
echo "⚙️ Configurando .env para instalação..."
cp "$TEMP_DIR/.env.example" "$TEMP_DIR/.env"

# Garantir que o install.php está presente
if [ ! -f "$TEMP_DIR/install.php" ]; then
    echo "❌ install.php não encontrado!"
    exit 1
fi

# Criar pastas de storage necessárias
mkdir -p "$TEMP_DIR/storage/app/public/produtos"
mkdir -p "$TEMP_DIR/storage/logs"
mkdir -p "$TEMP_DIR/bootstrap/cache"

# Criar arquivo .gitkeep nas pastas vazias
touch "$TEMP_DIR/storage/logs/.gitkeep"
touch "$TEMP_DIR/bootstrap/cache/.gitkeep"

# Criar o arquivo ZIP
echo "🗜️ Compactando em ${RELEASE_ZIP}..."
cd /tmp
zip -r "${OLDPWD}/${RELEASE_ZIP}" "${RELEASE_NAME}/" -q

# Limpar temporários
rm -rf "$TEMP_DIR"

cd "$OLDPWD"

# Mostrar resultado
if [ -f "$RELEASE_ZIP" ]; then
    SIZE=$(du -sh "$RELEASE_ZIP" | cut -f1)
    echo ""
    echo "================================================"
    echo "  ✅ Release criado com sucesso!"
    echo "================================================"
    echo "  Arquivo: ${RELEASE_ZIP}"
    echo "  Tamanho: ${SIZE}"
    echo ""
    echo "  Este arquivo está pronto para:"
    echo "  → Upload no Mercado Livre como produto digital"
    echo "  → Entrega imediata após pagamento"
    echo ""
    echo "  Lembre-se de testar antes de publicar:"
    echo "  1. Extraia o ZIP em uma pasta limpa"
    echo "  2. Rode: composer install"
    echo "  3. Rode: php artisan migrate --seed"
    echo "  4. Acesse: http://localhost:8000"
    echo "================================================"
else
    echo "❌ Erro ao criar o arquivo ZIP!"
    exit 1
fi
