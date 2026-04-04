<?php

namespace App\Listeners;

use App\Models\Invitation;
use App\Models\User;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;

class ProvisionCustomerOnRegistration
{
    /**
     * Create a Lunar Customer record for a newly registered user.
     *
     * Called directly from the registration controllers so first/last name are
     * accurate (no splitting required) and the invitation, if any, is available.
     *
     * Group assignment:
     *  - Staff invite with a target group  → that specific group
     *  - User-sent invite (referred_by_id) → inviter's non-default groups
     *  - All other registrations           → no group (admin assigns manually)
     */
    public function provision(User $user, string $firstName, string $lastName, ?Invitation $invitation = null): void
    {
        /** @var Customer $customer */
        $customer = Customer::create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ]);

        $customer->users()->attach($user->id);

        $this->assignGroups($customer, $invitation);
    }

    private function assignGroups(Customer $customer, ?Invitation $invitation): void
    {
        if ($invitation) {
            // Staff invite with an explicit target group.
            if ($invitation->is_staff_invite && $invitation->customer_group_id) {
                $group = CustomerGroup::find($invitation->customer_group_id);

                if ($group) {
                    $customer->customerGroups()->attach($group->id);

                    return;
                }
            }

            // User-sent invite — inherit the inviter's non-default groups.
            if (! $invitation->is_staff_invite && $invitation->invited_by_user_id) {
                $inviter = User::find($invitation->invited_by_user_id);

                if ($inviter) {
                    $inviterCustomer = $inviter->latestCustomer();

                    if ($inviterCustomer) {
                        $defaultGroupId = CustomerGroup::getDefault()?->id;

                        $nonDefaultGroups = $inviterCustomer->customerGroups
                            ->filter(fn ($g) => $g->id !== $defaultGroupId)
                            ->pluck('id');

                        if ($nonDefaultGroups->isNotEmpty()) {
                            $customer->customerGroups()->attach($nonDefaultGroups->toArray());

                            return;
                        }
                    }
                }
            }
        }

        // No group override — leave admin to assign group manually.
    }
}
