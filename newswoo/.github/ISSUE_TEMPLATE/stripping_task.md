---
name: Stripping Task
about: Track the removal of a WooCommerce retail subsystem
title: '[STRIP] '
labels: stripping, phase-2
assignees: ''
---

## Subsystem to Remove
<!-- Which WooCommerce retail subsystem is being stripped? -->
- [ ] Shipping & Fulfillment
- [ ] Inventory & Stock Management
- [ ] Cart System
- [ ] Coupon Complexity
- [ ] Tax/Geolocation Overhead
- [ ] Retail Product Types (Grouped, External, Variable)
- [ ] Other: ___

## Scope
**Files directly affected:** (number)
**Estimated cascading changes:** (number of files)

## Files to Modify
<!-- List the primary files that need changes -->
1. 
2. 
3. 

## Dependencies
<!-- What other subsystems depend on this one? What must be removed first? -->

## Testing Checklist
- [ ] All references removed from PHP files
- [ ] Database schema updated (install/upgrade)
- [ ] Admin UI cleaned up
- [ ] REST API endpoints removed/updated
- [ ] Email notifications removed/updated
- [ ] No PHP errors or warnings
- [ ] Existing subscription flows still work
- [ ] Existing membership flows still work

## Rollback Plan
How to revert if this causes unexpected issues.

## Related Issues
<!-- Link related stripping tasks -->

