# Feature Specification: Home Layout Updates

**Feature Branch**: `001-home-layout-updates`  
**Created**: 2026-03-03
**Status**: Draft  
**Input**: User description: "1- بدنا المقال اللي بكون بالهيرو ما يبين من ضمن البوستات اللي تحت 2- بدنا نعمل حقن للاعلانات داخل جريد البوستات مثلا كل 8 بوستات يطلع اعلامن 3- نزبط الية الأكثر قراءة هذا الأسبوع"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Exclude Hero Article from Grid (Priority: P1)

As a reader, I should not see the same article featured in the main hero section repeated again in the recent articles grid below it, so that I can discover more unique content on the homepage.

**Why this priority**: Preventing duplicate content on the homepage is critical for user experience and maximizes content discovery.

**Independent Test**: Can be fully tested by verifying that the lead article at the top of the homepage never appears in the subsequent generic lists or grids below it on the same page.

**Acceptance Scenarios**:

1. **Given** a user loads the homepage, **When** they view the hero section and then scroll down to the recent articles grid, **Then** the article featured in the hero section is not present in the grid.
2. **Given** the homepage is refreshed with new content, **When** the hero article changes, **Then** the new hero article is correctly excluded from the grid below it.

---

### User Story 2 - Fix "Most Read This Week" Mechanism (Priority: P1)

As a reader, when I look at the "Most Read This Week" section, I should see actual popular articles sorted by genuine views within the current week, so that I can discover trending content.

**Why this priority**: Displaying correct and accurate popular content is essential for credibility; currently, articles with 0 views are appearing or the ranking is incorrect.

**Independent Test**: Can be fully tested by verifying that the "Most Read This Week" list updates sequentially based on the highest view counts generated within the past 7 days, excluding items with 0 views if popular content exists.

**Acceptance Scenarios**:

1. **Given** articles have accumulated views in the past 7 days, **When** the "Most Read" widget is rendered, **Then** it displays articles ordered by the highest view count descending.
2. **Given** an article has 0 views, **When** the widget is rendered, **Then** the article does not appear at the top of the "Most Read" list if other articles have views.

---

### User Story 3 - Inject Ads in Articles Grid (Priority: P2)

As a user browsing the recent articles grid, I will see periodic advertisements seamlessly injected into the grid (e.g., every 8th post), so that the platform can monetize without severely disrupting the reading experience.

**Why this priority**: Ad revenue is important for the business, but must be balanced carefully with user layout experience.

**Independent Test**: Can be fully tested by counting the number of articles in the grid and verifying an advertisement block is correctly injected at the specified interval.

**Acceptance Scenarios**:

1. **Given** a grid with enough articles, **When** the user scrolls through the grid, **Then** an advertisement block is rendered after every configured batch of articles (e.g., after the 8th and 16th articles).
2. **Given** the grid is viewed on a mobile viewport, **When** the articles stack vertically, **Then** the ad injection maintains the defined sequence relative to the items.

---

### Edge Cases

- What happens to the ad injection if there are fewer articles than the defined interval (e.g., fewer than 8 articles)?
- How does the system handle "Most Read This Week" if all articles published this week have 0 views?
- What happens if multiple articles in "Most Read This Week" have the exact same view count?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST identify the article displayed in the main hero section of the homepage.
- **FR-002**: The system MUST explicitly exclude the hero article from the list of articles displayed in the main grid below.
- **FR-003**: The system MUST inject an advertisement block within the main articles grid at a configurable interval (default every 8 articles).
- **FR-004**: The system MUST accurately track reading views for each article over time.
- **FR-005**: The system MUST populate the "Most Read This Week" section with articles ordered strictly by their view counts accumulated within the last 7 days.
- **FR-006**: The system MUST ensure the injected advertisement block visually aligns with the grid layout without breaking the structural design.

### Key Entities

- **Article**: The core content unit, representing the news or post item to display.
- **Ad Block**: A designated zone for displaying advertisement content within the layout.
- **View Metric**: A numeric counter representing how many times an article has been read within a specific time window.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: The homepage loads with 0 duplicate articles between the hero section and the main grid.
- **SC-002**: Within the articles grid, exactly 1 ad unit is injected per every 8 article items.
- **SC-003**: The "Most Read This Week" list correctly reflects the top-viewed articles from the past 7 days, with 100% accuracy in sorting view counts descending.
