# Search Attributes for WooCommerce

## Development Setup

### Prerequisites
- WordPress with WooCommerce installed

## Steps to deploy the plugin

Update version number in:
* Main plugin file header
* readme.txt
* Any constants in your code

Update changelog in `readme.txt`

**Commit changes:**

```bash
git add .
git commit -m "Prepare release v1.2.3"
git tag 1.2.3  # Replace with your current tag
git push origin 1.2.3  # Replace with your current tag
```

Also do git push so that the main repo will be up to date. **This will not run the deploy workflow, so don’t worry**

```bash
git push
```

## To manually run the deploy

If the re-run option doesn't work, you can delete and re-push the same tag:

```bash
# Delete the tag locally
git tag -d 1.2.3  # Replace with your current tag

# Delete the tag on GitHub
git push origin :refs/tags/1.2.3  # Replace with your current tag

# Create the tag again
git tag 1.2.3  # Replace with your current tag

# Push the tag again
git push origin 1.2.3  # Replace with your current tag
```