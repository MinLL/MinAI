#!/bin/bash
# Install MinAI Minimal

echo "Installing MinAI Minimal..."

# Backup original minai_plugin if it exists
if [ -d "minai_plugin" ]; then
    echo "Backing up original minai_plugin to minai_plugin_backup..."
    mv minai_plugin minai_plugin_backup
fi

# Copy minimal version
echo "Installing minimal version..."
cp -r minai_minimal minai_plugin

# Create symlink for easier access
ln -sf minai_plugin/integration.php minai_integration.php

echo "MinAI Minimal installed successfully!"
echo ""
echo "Features included:"
echo "- Self Narrator: Internal player thoughts and reactions"
echo "- Translation: Convert casual speech to character voice"
echo ""
echo "Configuration: Edit minai_plugin/config.php"
echo "Logs: /var/www/html/HerikaServer/log/minai_minimal.log"
echo ""
echo "To integrate with HerikaServer, include:"
echo "require_once('/path/to/minai_plugin/integration.php');"