# Upgrade from 2.x to 3.0

## Types

Type declarations were added throughout the codebase for properties, parameters, and return values.
If you use custom implementations (like a custom entity), you'll probably encounter PHP errors if their type declarations don't match those of the base class.
Update your extended/overridden code to include the appropriate type declarations.
