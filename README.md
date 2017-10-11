# Architect

Architect is a command line code generator for the Blueprint database package, to be used with the Chassis framework.

### Usage

From the project root, run:

`bin/architect database_prefix table_name optional_template_filename`

Architect will attempt to generate a Data class from either the optional template file provided, or the default contained in the package. The database prefix is the same as used in the Chassis framework .env file. The table name is the table to be used as the primary table in the template.

Blueprint Data classes do not have a one-to-one relationship with individual tables. Generated classes here should be used as a base to flesh out additional complex relations.

New Data classes will be stored in `/src/DBAL`.