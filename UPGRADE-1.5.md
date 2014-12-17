UPGRADE FROM 1.4 to 1.5
=======================

####OroCRMContactUsBundle:
 - `ContactRequest` entity was made *extended*. *Note: Field changes will affect all embedded forms that based on contact request entity.*
 - Added possibility to hide *extended* fields from embedded form. Each field is wrapped in `div` element with unique identifier,
    so fields visibility and styles could be applied for any embedded form individually via **css** customization. 
