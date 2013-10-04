Workflows
=========

Table of Contents
-----------------
 - [Workflows Diagram](#workflows-diagram)
 - [Unqualified Sales Lead](#unqualified-sales-lead)
 - [Qualified Sales Opportunity](#qualified-sales-opportunity)
 - [Sales Flow](#sales-flow)

Workflows Diagram
-----------------

Following diagram shows available workflows steps and transitions.

![Workflows](../images/workflows.png)


Unqualified Sales Lead
----------------------

* **Managed entity:** Lead (OroCRM\Bundle\SalesBundle\Entity\Lead).
* **Workflow Type:** entity

### Steps And Allowed Transitions

**New Step**
* Qualify
* Cancel

**Cancelled Step**
* Reactivate

**Qualified Step**
* Reactivate

### Transitions

#### Qualify

**Conditions:**
 * *Lead* has *New* status

**Post Actions:**
 * Switch *Lead* status to *Qualified*
 * If *Lead* doesn't have *Contact* entity create it with first name, last
 name, job title, and name as a description of *Contact*
 * If *Lead* has *Address* entity add it to *Contact* as *Contact Address*
 * If *Lead* has *Email* entity, add it to *Contact*
 * If *Lead* has *Phone* entity, add it to *Contact*
 * Create *Opportunity* entity based on *Lead* with *In Progress*
 status
 * Start workflow *Sales Flow* with created *Opportunity*
 * Redirect user to created workflow

**Step To:** Qualified

#### Reactivate

**Conditions:**
 * *Lead* has *Qualified* status

**Post Actions:**
 * Switch *Lead* status to *Cancelled*

**Step To:** New

#### Cancel

**Conditions:**
 * *Lead* has *New* status

**Post Actions:**
 * Switch *Lead* status to *Cancelled*

**Step To:** Cancelled


Qualified Sales Opportunity
---------------------------

* **Managed entity:** Opportunity (OroCRM\Bundle\SalesBundle\Entity\Opportunity).
* **Workflow Type:** entity

### Steps And Allowed Transitions

**New Step**
* Close As Won
* Close As Lost
* Reopen

**Close Step**
* Reopen

### Transitions

#### Close As Won

**Conditions:**
 * *Opportunity* has *In Progress* status

**Post Actions:**
 * Switch *Opportunity* status to *Won*

**Step To:** Close

#### Close As Lost

**Conditions:**
 * *Opportunity* has *In Progress* status

**Post Actions:**
 * Switch *Opportunity* status to *Lost*

**Step To:** Close

#### Reopen

**Conditions:**
 * *Opportunity* has *Won* or *Lost* status

**Post Actions:**
 * Switch *Opportunity* status to *In Progress*

**Step To:** New

Sales Flow
----------

* **Managed entity:** Opportunity (OroCRM\Bundle\SalesBundle\Entity\Opportunity).
* **Workflow Type:** wizard

### Steps

#### Develop Step

**Attributes**
* Account
* Contact
* Budget
* Probability
* Customer Need
* Proposed Solution

**Allowed Transitions**
* Close

#### Close Step**

**Attributes**
* Close Reason
* Close Revenue
* Close Date

**Allowed Transitions**
* Close As Won
* Close As Lost

### Transitions

#### Develop

**Conditions:**
 * *Opportunity* has *In Progress* status

**Post Actions:**
 * import values of *Opportunity* to workflow

**Step To:** Develop

#### Close

**Conditions:**
 * *Opportunity* has *In Progress* status
 * *Probability* is between 0 and 1

**Post Actions:**
 * import values of *Opportunity* to workflow
 * set *Close Revenue* to 0

**Step To:** Close

#### Close As Won

**Conditions:**
 * *Opportunity* has *In Progress* status
 * *Close Date* is not empty
 * *Close Revenue* is not empty
 * *Close Reason* is not empty
 * *Probability* is between 0 and 1
 * *Close Reason* is set to "Won"

**Post Actions:**
 * Set *Probability* to 1
 * Set values of *Close Data*, *Close Revenue*, *Close Reason*, *Probability* to *Opportunity*
 * Switch *Opportunity* status to *Won*
 * Close workflow
 * Redirect to *Opportunity* view page

**Step To:** Close

#### Close As Lost

**Conditions:**
 * *Opportunity* has *In Progress* status
 * *Close Date* is not empty
 * *Close Revenue* is not empty
 * *Close Reason* is not empty
 * *Probability* is between 0 and 1
 * *Close Reason* is set to "Cancelled" or "Outsold"

**Post Actions:**
 * Set *Probability* to 0
 * Set values of *Close Data*, *Close Revenue*, *Close Reason*, *Probability* to *Opportunity*
 * Switch *Opportunity* status to *Lost*
 * Close workflow
 * Redirect to *Opportunity* view page

**Step To:** Close
