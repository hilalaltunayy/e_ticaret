"""Non-secret local runtime configuration."""

from pathlib import Path


REPO_ROOT = Path("/workspace")
OUTPUT_DIR = REPO_ROOT / "ai" / "oracle" / "runtime" / "output"
READ_ONLY_REPO = True
