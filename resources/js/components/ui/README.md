# UI Components

Reusable UI components following the Metaverse Admin theme with glassmorphism, gradients, and neon accents.

## Components

### Button

A versatile button component with multiple variants and sizes.

```vue
<script setup>
import { Button } from '@/components/ui'

const handleClick = () => {
  console.log('Clicked!')
}
</script>

<template>
  <!-- Primary gradient button -->
  <Button variant="primary" @click="handleClick">
    Primary Button
  </Button>

  <!-- Secondary button -->
  <Button variant="secondary" size="lg">
    Secondary
  </Button>

  <!-- With loading state -->
  <Button variant="primary" :loading="isLoading">
    Submit
  </Button>

  <!-- With icons -->
  <Button variant="primary">
    <template #icon-left>
      <svg>...</svg>
    </template>
    Save
  </Button>
</template>
```

**Props:**
- `variant`: `primary` | `secondary` | `danger` | `success` | `warning` | `outline` | `ghost` | `glass`
- `size`: `xs` | `sm` | `md` | `lg` | `xl`
- `rounded`: `none` | `sm` | `md` | `lg` | `full`
- `loading`: Boolean
- `disabled`: Boolean
- `fullWidth`: Boolean

---

### Card

Glassmorphic card component with optional gradient borders.

```vue
<script setup>
import { Card } from '@/components/ui'
</script>

<template>
  <!-- Basic card -->
  <Card title="Card Title" subtitle="Subtitle text">
    Card content goes here
  </Card>

  <!-- Glass variant -->
  <Card variant="glass" title="Glass Card">
    Glassmorphic content
  </Card>

  <!-- With slots -->
  <Card>
    <template #header>
      <h3>Custom Header</h3>
    </template>
    Content
    <template #footer>
      <Button>Action</Button>
    </template>
  </Card>
</template>
```

**Props:**
- `variant`: `elevated` | `glass` | `flat`
- `rounded`: `none` | `sm` | `md` | `lg` | `xl`
- `padding`: `none` | `sm` | `md` | `lg` | `xl`
- `title`: String
- `subtitle`: String
- `gradientBorder`: Boolean
- `hoverGlow`: Boolean

---

### Input

Text input component with validation states and icons.

```vue
<script setup>
import { Input } from '@/components/ui'
import { ref } from 'vue'

const email = ref('')
const password = ref('')
const error = ref('')
</script>

<template>
  <!-- Basic input -->
  <Input
    v-model="email"
    label="Email"
    type="email"
    placeholder="Enter your email"
    helper-text="We'll never share your email"
  />

  <!-- With error -->
  <Input
    v-model="email"
    label="Email"
    error="Please enter a valid email"
  />

  <!-- Password with toggle -->
  <Input
    v-model="password"
    label="Password"
    type="password"
  />

  <!-- With icons -->
  <Input
    v-model="email"
    label="Search"
    placeholder="Search..."
  >
    <template #icon-left>
      <svg>...</svg>
    </template>
  </Input>
</template>
```

**Props:**
- `modelValue`: String | Number
- `type`: String (text, email, password, etc.)
- `label`: String
- `placeholder`: String
- `helperText`: String
- `error`: String
- `disabled`: Boolean
- `readonly`: Boolean
- `required`: Boolean
- `size`: `sm` | `md` | `lg`
- `iconLeft`: Component
- `iconRight`: Component

---

### Modal

Full-featured modal/dialog component with backdrop and animations.

```vue
<script setup>
import { Modal, Button } from '@/components/ui'
import { ref } from 'vue'

const showModal = ref(false)
</script>

<template>
  <Button @click="showModal = true">Open Modal</Button>

  <Modal
    v-model="showModal"
    title="Confirm Action"
    subtitle="Are you sure you want to proceed?"
    size="md"
  >
    <p>This action cannot be undone.</p>
    
    <template #footer>
      <Button variant="ghost" @click="showModal = false">Cancel</Button>
      <Button variant="primary" @click="handleConfirm">Confirm</Button>
    </template>
  </Modal>
</template>
```

**Props:**
- `modelValue`: Boolean
- `title`: String
- `subtitle`: String
- `size`: `sm` | `md` | `lg` | `xl` | `full`
- `closable`: Boolean
- `closeOnBackdrop`: Boolean
- `closeOnEscape`: Boolean

---

### Dropdown

Dropdown menu component with customizable placement.

```vue
<script setup>
import { Dropdown } from '@/components/ui'
import { ref } from 'vue'

const isOpen = ref(false)
const items = [
  { label: 'Profile', icon: UserIcon },
  { label: 'Settings', icon: SettingsIcon },
  { label: 'Logout', danger: true }
]
</script>

<template>
  <Dropdown
    v-model="isOpen"
    label="Menu"
    :items="items"
    placement="bottom-end"
    @item-click="handleItemClick"
  />
</template>
```

**Props:**
- `modelValue`: Boolean
- `label`: String
- `items`: Array
- `placement`: String (top-start, top, top-end, bottom-start, bottom, bottom-end, etc.)
- `closeOnClick`: Boolean

---

### Badge

Status badge component with variants and icons.

```vue
<script setup>
import { Badge } from '@/components/ui'
</script>

<template>
  <!-- Status badges -->
  <Badge variant="success">Active</Badge>
  <Badge variant="warning">Pending</Badge>
  <Badge variant="danger">Inactive</Badge>

  <!-- With icon -->
  <Badge variant="primary" :icon="CheckIcon">
    Verified
  </Badge>
</template>
```

**Props:**
- `variant`: `default` | `primary` | `secondary` | `success` | `warning` | `danger` | `info` | `glass`
- `size`: `sm` | `md` | `lg`
- `icon`: Component

---

### Spinner

Loading spinner component with customizable size and color.

```vue
<script setup>
import { Spinner } from '@/components/ui'
</script>

<template>
  <!-- Basic spinner -->
  <Spinner />

  <!-- With text -->
  <Spinner size="lg" show-text text="Loading data..." />

  <!-- Custom color -->
  <Spinner size="xl" color="#7C3AED" />
</template>
```

**Props:**
- `size`: `xs` | `sm` | `md` | `lg` | `xl`
- `color`: String (CSS color)
- `text`: String
- `showText`: Boolean

---

### Alert

Alert/notification component with variants.

```vue
<script setup>
import { Alert } from '@/components/ui'
import { ref } from 'vue'

const showAlert = ref(true)
</script>

<template>
  <!-- Success alert -->
  <Alert
    v-model="showAlert"
    variant="success"
    title="Success!"
    message="Your changes have been saved."
    dismissible
  />

  <!-- Error alert -->
  <Alert
    variant="danger"
    title="Error"
    message="Something went wrong."
  />

  <!-- Info alert -->
  <Alert
    variant="info"
    message="New update available."
    dismissible
  />
</template>
```

**Props:**
- `modelValue`: Boolean
- `variant`: `success` | `warning` | `danger` | `info`
- `title`: String
- `message`: String
- `dismissible`: Boolean
- `icon`: Component

---

### Avatar

User avatar component with status indicators.

```vue
<script setup>
import { Avatar } from '@/components/ui'
</script>

<template>
  <!-- With image -->
  <Avatar src="/path/to/image.jpg" alt="User" />

  <!-- With initials -->
  <Avatar initials="JD" size="lg" />

  <!-- With status -->
  <Avatar
    initials="AB"
    status="online"
    ring
  />

  <!-- With fallback -->
  <Avatar
    fallback="Admin"
    size="xl"
    gradient
  />
</template>
```

**Props:**
- `src`: String
- `alt`: String
- `fallback`: String
- `initials`: String
- `size`: `xs` | `sm` | `md` | `lg` | `xl` | `2xl`
- `rounded`: `none` | `sm` | `md` | `lg` | `full`
- `status`: `online` | `offline` | `away` | `busy`
- `ring`: Boolean
- `gradient`: Boolean

---

### Select

Styled select dropdown component.

```vue
<script setup>
import { Select } from '@/components/ui'
import { ref } from 'vue'

const selected = ref('')
const options = [
  { value: '1', label: 'Option 1' },
  { value: '2', label: 'Option 2' },
  { value: '3', label: 'Option 3' }
]
</script>

<template>
  <Select
    v-model="selected"
    label="Choose Option"
    placeholder="Select an option"
    :options="options"
    helper-text="Select from the dropdown"
  />
</template>
```

**Props:**
- `modelValue`: String | Number
- `label`: String
- `placeholder`: String
- `options`: Array
- `optionValue`: String (default: 'value')
- `optionLabel`: String (default: 'label')
- `helperText`: String
- `error`: String
- `disabled`: Boolean
- `required`: Boolean
- `size`: `sm` | `md` | `lg`

---

## Importing Components

You can import components individually or from the barrel export:

```javascript
// Individual imports
import Button from '@/components/ui/Button.vue'
import Card from '@/components/ui/Card.vue'

// Barrel export (recommended)
import { Button, Card, Input, Modal } from '@/components/ui'
```

## Theme Integration

All components automatically use the Metaverse Admin theme variables defined in `app.css`. They support:
- Dark/Light mode switching
- Glassmorphism effects
- Gradient accents
- Neon glow effects
- Responsive design

## Customization

Components can be customized through props and CSS variables. Theme colors are defined in:
- `resources/css/app.css` (Tailwind theme configuration)

