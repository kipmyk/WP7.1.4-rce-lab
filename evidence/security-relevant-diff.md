# Security-Relevant Diff Analysis (GHSA-4w3m-c66w-69mg)

This document contains the verified Git diffs between official WordPress 7.1.3 and 7.1.4 source distributions for the files modified under Trac Changeset `64877` addressing advisory **GHSA-4w3m-c66w-69mg**.

---

## 1. Upgrade Staging Directory Creation & Guard Preservation

### File: `wp-admin/includes/class-wp-upgrader.php`

```diff
--- vulnerable/html/wp-admin/includes/class-wp-upgrader.php	2026-06-19 12:46:44
+++ patched/html/wp-admin/includes/class-wp-upgrader.php	2026-10-28 14:55:48
@@ -341,6 +341,21 @@
 		}
 
 		return $download_file;
+	}
+
+	/**
+	 * Builds an unpredictable name for the directory a package is unpacked into.
+	 *
+	 * @since 7.1.4
+	 *
+	 * @param string $package The full local path or URI of the package.
+	 * @return string The directory name, without a path.
+	 */
+	private function get_working_dir_name( $package ) {
+		// Strip off any .tmp or .zip suffixes.
+		$name = basename( basename( $package, '.tmp' ), '.zip' );
+
+		return $name . '-' . wp_generate_password( 16, false );
 	}
 
 	/**
@@ -366,17 +381,33 @@
 
 		$upgrade_folder = $wp_filesystem->wp_content_dir() . 'upgrade/';
 
-		// Clean up contents of upgrade directory beforehand.
+		// Clean up contents of upgrade directory beforehand, keeping its index.php.
 		$upgrade_files = $wp_filesystem->dirlist( $upgrade_folder );
 		if ( ! empty( $upgrade_files ) ) {
 			foreach ( $upgrade_files as $file ) {
+				if ( 'index.php' === $file['name'] ) {
+					continue;
+				}
+
 				$wp_filesystem->delete( $upgrade_folder . $file['name'], true );
 			}
 		}
 
-		// We need a working directory - strip off any .tmp or .zip suffixes.
-		$working_dir = $upgrade_folder . basename( basename( $package, '.tmp' ), '.zip' );
+		/*
+		 * The upgrade directory is web-served. Keep it from being listed, as core does
+		 * for wp-content and its plugins and themes directories, so the name of the
+		 * working directory created below cannot be discovered from a directory index.
+		 */
+		if ( ! $wp_filesystem->is_dir( $upgrade_folder ) ) {
+			$wp_filesystem->mkdir( $upgrade_folder, FS_CHMOD_DIR );
+		}
+		if ( ! $wp_filesystem->exists( $upgrade_folder . 'index.php' ) ) {
+			$wp_filesystem->put_contents( $upgrade_folder . 'index.php', "<?php\n// Silence is golden.\n", FS_CHMOD_FILE );
+		}
 
+		// We need a working directory.
+		$working_dir = $upgrade_folder . $this->get_working_dir_name( $package );
+
 		// Clean up working directory.
 		if ( $wp_filesystem->is_dir( $working_dir ) ) {
 			$wp_filesystem->delete( $working_dir, true );
```

---

## 2. File Synchronization Exclusion of `wp-config.php`

### File: `wp-admin/includes/update-core.php`

```diff
--- vulnerable/html/wp-admin/includes/update-core.php	2026-08-16 23:48:56
+++ patched/html/wp-admin/includes/update-core.php	2026-10-28 14:55:48
@@ -1545,8 +1545,9 @@
 	/*
 	 * Don't copy wp-content, we'll deal with that below.
 	 * We also copy version.php last so failed updates report their old version.
+	 * Never copy a wp-config.php, which could have been written to the working directory.
 	 */
-	$skip              = array( 'wp-content', 'wp-includes/version.php' );
+	$skip              = array( 'wp-content', 'wp-includes/version.php', 'wp-config.php' );
 	$check_is_writable = array();
 
 	// Check to see which files don't really need updating - only available for 3.7 and higher.
```
