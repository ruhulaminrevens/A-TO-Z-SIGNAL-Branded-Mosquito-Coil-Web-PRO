# Repository Asset Policy

This repository contains the complete functional v44 application, database schema, version history, documentation and optimized production assets.

## Optimized WebP sources

The application prefers WebP variants through `optimized_image_url()`. During the GitHub import, the following nine oversized legacy PNG originals were not duplicated because their matching WebP assets are present and used by the application:

- `assets/img/logo-atoz.png`
- `assets/img/logo-signal.png`
- `uploads/products/atoz-jumbo-coil-1781767109.png`
- `uploads/products/atoz-super-power-coil-1781767306.png`
- `uploads/products/atoz-turbo-coil-1781765992.png`
- `uploads/products/atoz-turbo-coil-1781767551.png`
- `uploads/products/signal-jumbo -coil-1781766273.png`
- `uploads/products/signal-mega-coil-1781767447.png`
- `uploads/products/signal-plus-coil-1781757771.png`

Each omitted PNG has a same-name `.webp` file in the repository. No public page loses its displayed image.

The exact archival v44 ZIP remains the authoritative package for byte-for-byte recovery.
